<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Points
 * @version    1.5.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AW_Points_Model_Cron {

    /**
     *  Cron launches  the function once a day (see crontab settings in config.xml)
     */
    public function checkAndCleanExpiredTransactions() {

        $this
                ->_cleanExpiredTransactions()
                ->_sendWarningLetter();
    }

    /**
     * Cleans expired transactions. If transaction is expired, balance_change_spent becomes = balance_change
     */
    protected function _cleanExpiredTransactions() {
        // Remove old locks
        Mage::getModel('points/transaction')->getCollection()->addOldLockedFilter()->unlock();

        // Lock table for writing
        $expiredTransactions = Mage::getModel('points/transaction')->getCollection()
                ->addBalanceActiveFilter()
                ->addNotLockedFilter()
                ->addExpiredFilter();

        $expiredTransactions->getResource()->getReadConnection()->raw_query('LOCK TABLE ' . $expiredTransactions->getMainTable() . " as main_table READ");

        $ids = array();
        foreach ($expiredTransactions as $tr) {
            $ids[] = $tr->getId();
        }

        $expiredTransactions->getResource()->getReadConnection()->raw_query('UNLOCK TABLES');

        // Set lock
        $expiredTransactions->lock();

        foreach ($ids as $transactionId) {
            $transaction = Mage::getModel('points/transaction')->load($transactionId);
            $result = Mage::getModel('points/api')->addTransaction(
                    $transaction->getBalanceChangeSpent() - $transaction->getBalanceChange(), 'transaction_expired', $transaction->getCustomer(), $transaction, array('transaction_id' => $transaction->getId())
            );
        }


        return $this;
    }

    protected function _sendWarningLetter() {

        if (Mage::helper('points/config')->getIsEnabledNotifications()) {

            //Send points expire email before
            $sendBeforeDays = Mage::helper('points/config')->getDaysBeforePointExpiredToSendEmail();
            $transactionsToWarn = Mage::getModel('points/transaction')
                    ->getCollection()
                    ->addBalanceActiveFilter()
                    ->addExpiredAfterDaysFilter($sendBeforeDays);


            $summaryData = array();
            $currentDate = new Zend_Date(date('Y-m-d'));

            foreach ($transactionsToWarn as $transaction) {

                $summaryId = $transaction->getSummaryId();
                if (!isset($summaryData[$summaryId])) {
                    $summaryData[$summaryId] = array();
                }

                $expirationDate = $transaction->getData('expiration_date');

                $expirationDate = new Zend_Date($expirationDate);
                $expirationDate = new Zend_Date($expirationDate->toString(Varien_Date::DATE_INTERNAL_FORMAT));

                $daysLeft = $expirationDate->sub($currentDate)->toValue();
                $daysLeft = floor($daysLeft / (60 * 60 * 24));

                if (!isset($summaryData[$summaryId][$daysLeft])) {
                    $summaryData[$summaryId][$daysLeft] = (int) $transaction->getData('balance_change');
                } else {
                    $summaryData[$summaryId][$daysLeft]+=$transaction->getData('balance_change');
                }
            }


            $mail = Mage::getModel('core/email_template');
            foreach ($summaryData as $summaryId => $pointsExpData) {

                $summary = Mage::getModel('points/summary')->load($summaryId);

                if ($summary->getPointsExpirationNotification()) {

                    $customer = $summary->getCustomer();
                    $store = $customer->getStore();
                    $pointUnitName = Mage::helper('points/config')->getPointUnitName($store->getStoreId());


                    Mage::unregister('aw_points_unit_name');
                    Mage::register('aw_points_unit_name', $pointUnitName);

                    Mage::unregister('aw_points_exp_data');
                    Mage::register('aw_points_exp_data', $pointsExpData);

                    $mailParams = array(
                        'store' => $store,
                        'customer' => $customer,
                        'pointsname' => $pointUnitName,
                        'pointstoexpire' => array_sum($pointsExpData),
                        'expirationdays' => $sendBeforeDays,
                    );

                    try {

                        $mail->setDesignConfig(array('area' => 'frontend', 'store' => $store->getStoreId()))
                                ->sendTransactional(
                                        Mage::helper('points/config')->getPointsExpireTemplate($store->getStoreId()), Mage::helper('points/config')->getNotificatioinSender($store->getStoreId()), $customer->getEmail(), null, $mailParams
                        );
                    } catch (Exception $exc) {
                        $logMessage = Mage::helper('points')->__('Unable to send %s expiration email.', $pointUnitName) . '  ' . $exc->getMessage();
                        Mage::helper('awcore/logger')->log($this, $logMessage, AW_Core_Model_Logger::LOG_SEVERITY_WARNING);
                    }

                    if ($mail->getSentSuccess()) {
                        foreach ($transactionsToWarn as $transaction) {
                            if ($transaction->getSummaryId() == $summaryId) {
                                $transaction->setData('expiration_notification_sent', true)->save();
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

}
