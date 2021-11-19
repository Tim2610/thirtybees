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
 * Class AdminOrderMessageControllerCore
 *
 * @since 1.0.0
 */
class AdminSystemNotificationControllerCore extends AdminController
{
  // @codingStandardsIgnoreStart
  protected $delete_mode;
  protected $_defaultOrderBy = 'importance';
  protected $_defaultOrderWay = 'asc';
  // @codingStandardsIgnoreEnd

    /**
     * AdminOrderMessageControllerCore constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'system_notification';
        $this->className = 'SystemNotification';
        $this->lang = false;

        $this->addRowAction('view');


        $this->context = Context::getContext();

        $this->default_form_language = $this->context->language->id;

        if (!Tools::getValue('realedit')) {
            $this->deleted = false;
        }

        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon'    => 'icon-trash',
            ],
        ];


        $this->fields_list = [
            'id_system_notification' => [
                'title' => $this->l('ID'),
                'align' => 'center',
            ],
            'short_desc'             => [
                'title' => $this->l('short_desc'),
            ],
            'description'          => [
                'title'     => $this->l('description'),
                'maxlength' => 300,
            ],
            'importance'          => [
                'title'     => $this->l('importance'),
            ],
            'date_add'          => [
                'title'     => $this->l('date_add'),
            ],
            'date_upd'          => [
                'title'     => $this->l('date_upd'),
            ],
        ];



        parent::__construct();
    }




    public function initToolbarTitle()
    {
        parent::initToolbarTitle();

        switch ($this->display) {
            case '':
            case 'list':
                array_pop($this->toolbar_title);
                $this->toolbar_title[] = $this->l('SystemNotifications');
                break;
            case 'view':
                /** @var Customer $customer */
                if (($system_notification = $this->loadObject(true)) && Validate::isLoadedObject($system_notification)) {
                    array_pop($this->toolbar_title);
                    $this->toolbar_title[] = sprintf($this->l('Information about System notification: %s'), mb_substr($system_notification->id, 0, 1));
                }
                break;

                // /** @var SystemNotification $system_notification */
                // if (($system_notification = $this->loadObject(true)) && Validate::isLoadedObject($system_notification)) {
                //     array_pop($this->toolbar_title);
                //     $this->toolbar_title[] = sprintf($this->l($system_notification->short_desc));
                // }
                // else{
                //   $this->addMetaTitle($this->toolbar_title[count($this->toolbar_title) - 1]);
                // }

    }
  }

    public function renderView()
    {
        /** @var SystemNotification $system_notification */
        if (!($system_notification = $this->loadObject())) {
            return;
        }

        $this->context->system_notification = $system_notification;

        $this->tpl_view_vars = [
            'system_notification' => $system_notification,
            'id'               => $system_notification->id_system_notification,
            'short_desc'       => $system_notification->short_desc,
            'description'      => $system_notification->description,
            'date_add'       => Tools::displayDate($system_notification->date_add, null, true),
            'date_upd'       => Tools::displayDate($system_notification->date_upd, null, true),
            'importance'       => $system_notification->importance,

            'show_toolbar'           => true,
        ];

        return parent::renderView();
    }

}
