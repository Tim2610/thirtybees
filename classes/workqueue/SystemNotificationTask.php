<?php
/**
 * Copyright (C) 2021-2021 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @copyright 2021-2021 thirty bees
 * @license   Open Software License (OSL 3.0)
 */

namespace Thirtybees\Core\Notification;


use Adapter_Exception;
use Configuration;
use Db;
use Exception;
use PrestaShopException;
use Thirtybees\Core\InitializationCallback;
use Thirtybees\Core\WorkQueue\ScheduledTask;
use Thirtybees\Core\WorkQueue\WorkQueueContext;
use Thirtybees\Core\WorkQueue\WorkQueueTaskCallable;

/**
 * Class TrackingTaskCore
 *
 * Work queue task that collects information and sends them to thirty bees api server
 *
 * @since 1.3.0
 */
class SystemNotificationTask implements WorkQueueTaskCallable, InitializationCallback
{

    /**
     * Task execution method
     *
     * Collect data using all extractors that store owner gave consent. If any data are available,
     * send them to thirty bees api server
     *
     *
     * @param WorkQueueContext $context
     * @param array $parameters
     *
     * @throws Exception
     * @return mixed
     */
     public function execute(WorkQueueContext $context, array $parameters)
     {
         $guzzle = new \GuzzleHttp\Client([
             'base_uri'    => Configuration::getApiServer(),
             'timeout'     => 15,
             'verify'      => _PS_TOOL_DIR_.'cacert.pem'
         ]);

         // TODO: prepare request payload
         $payload = [
             'sinceId' => 1
         ];

         $response = $guzzle->post(
             '/notification/v1.php',
             [
                 'json' => $payload
             ]
         );

         $body = $response->getBody();
         $json = json_decode($body, true);
         $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
         if ($json['success']) {
             $data = $json['data'];
             foreach ($data as $notificationData) {
                 if ($this->acceptNotification($notificationData['condition'])) {
                     $systemNotication = new SystemNotificationTask();
                     // TODO: prepare object and save it
                     $SystemNotification->id = $notificationData['id'];
                     $SystemNotification->date_add = $notificationData['date'];
                     $SystemNotification->importance = $notificationData['importance'];
                     $SystemNotification->description = $notificationData['description'];
                     $SystemNotification->short_desc = $notificationData['shortDesc'];
                     // $systemNotication->add();

                     // var_dump($SystemNotification);

                     $requestidCheck = "SELECT `id_system_notification` FROM `tb_system_notification` WHERE `id_system_notification` = $SystemNotification->id";
                     $checkNotificationExists = $db->getRow($requestidCheck);
                     // var_dump($checkNotificationExists);
                     if(!$checkNotificationExists){
                       $request = "INSERT INTO `tb_system_notification` (`id_system_notification`, `date_add`, `date_upd`, `importance`, `short_desc`, `description`)
                       VALUES ($SystemNotification->id,  now(), now(),  $SystemNotification->importance,  '$SystemNotification->short_desc', '$SystemNotification->description')";
                       $result = $db->execute($request);
                     }
                     // TODO: logging
                 }
             }
             $request = "INSERT INTO `tb_system_notification` (`id_system_notification`, `date_add`, `date_upd`, `importance`, `short_desc`, `description`)
             VALUES (15,  now(), now(),  1,  'short Test description', 'long Test description')";
             $result = $db->execute($request);

         } else {
             // TODO: handle error -- logging, etc...

         }
     }

     public function acceptNotification($condition)
     {
         // TODO: implement condition parsing, not part of current stage
         return true;
     }

    /**
     * Callback method to initialize class
     *
     * @param Db $conn
     * @return void
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    public static function initializationCallback(Db $conn)
    {
        $task = str_replace("SystemNotificationTaskCore", "SystemNotificationTask", static::class);
        $notificationTask = ScheduledTask::getTasksForCallable($task);
        if (! $notificationTask) {
            $scheduledTask = new ScheduledTask();
            $scheduledTask->frequency = rand(0, 1) . ' ' . rand(0, 1) . ' * * *';
            $scheduledTask->name = 'Thirty bees data collection task';
            $scheduledTask->description = 'Sends various information to thirty bees server';
            $scheduledTask->task = $task;
            $scheduledTask->active = true;
            $scheduledTask->add();
        }
    }
}
