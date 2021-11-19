<?php
/**
* 2007-2016 PrestaShop
*
* thirty bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
* Copyright (C) 2017-2018 thirty bees
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@thirtybees.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to https://www.thirtybees.com for more information.
*
* @author    Tim Klomp
* @copyright 2017-2018 thirty bees
* @copyright 2007-2016 PrestaShop SA
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  PrestaShop is an internationally registered trademark & property of PrestaShop SA
*/

/**
* Class ContactCore
*
* @since 1.0.0
*/
class SystemNotificationCore extends ObjectModel
{
  public $id;
  /** @var string short description of notification */
  public $short_desc;
  /** @var string detailed description */
  public $description;
  /** @var datetime date created notification*/
  public $date_add;
  /** @var integer importance level */
  public $date_upd;
  /** @var integer importance level */
  public $importance;
  /** @var $short_descriprion */

// @codingStandardsIgnoreEnd

/**
* @see ObjectModel::$definition
*/
public static $definition = [
  'table'     => 'system_notification',
  'primary'   => 'id_system_notification',
  'multilang' => false,
  'fields'    => [
    'date_add'          =>['type' => self::TYPE_DATE, 'validate' => 'isDate'],
    'date_upd'          =>['type' => self::TYPE_DATE, 'validate' => 'isDate'],
    'importance'          =>['type' => self::TYPE_INT, 'validate' => 'tinyint(1)'],
    'short_desc'          =>['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => ObjectModel::SIZE_TEXT],
    'description'         =>['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => ObjectModel::SIZE_TEXT],
  ],
];

/**
 * @param string|null $where
 *
 * @return int
 *
 * @since   1.0.0
 * @version 1.0.0 Initial version
 * @throws PrestaShopException
 * @throws PrestaShopException
 */
// public static function getTotalSystemNotifications($where = null)
// {
//   if(is_null($where)){
//     return (int) Db::getInstance(_PD_USE_SQL_SLAVE_)->getValue(
//       (new DbQuery())
//         ->select('COUNT(*)')
//         ->from('system_notification')
//         ->where('1'.Shop::addSqlRestriction())
//     );
//   } else{
//       return (int) Db::getInstance()->getValue(
//         (new DbQuery())
//           ->select('COUNT(*)')
//           ->from('system_notification')
//           ->where($where.Shop::addSqlRestriction())
//         );
//   }
// }


}
