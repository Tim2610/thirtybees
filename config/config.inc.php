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
 *  @author    thirty bees <contact@thirtybees.com>
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2017-2018 thirty bees
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

$currentDir = dirname(__FILE__);

/* Custom defines made by users */
if (is_file($currentDir.'/defines_custom.inc.php')) {
    include_once($currentDir.'/defines_custom.inc.php');
}
require_once($currentDir.'/defines.inc.php');

$start_time = microtime(true);

/* SSL configuration */
define('_PS_SSL_PORT_', 443);

/* Improve PHP configuration to prevent issues */
ini_set('default_charset', 'utf-8');

/* correct Apache charset (except if it's too late */
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

/* No settings file? goto installer... */
if (!file_exists(_PS_ROOT_DIR_.'/config/settings.inc.php')) {
    if (file_exists($currentDir.'/../install')) {
        header('Location: install/');
    } elseif (file_exists($currentDir.'/../install-dev')) {
        header('Location: install-dev/');
    } else {
        die('Error: "install" directory is missing');
    }
    exit;
}

/* include settings file only if we are not in multi-tenancy mode */
require_once(_PS_ROOT_DIR_.'/config/settings.inc.php');
require_once(_PS_CONFIG_DIR_.'autoload.php');

if (! defined('_TB_REVISION_')) {
    define('_TB_REVISION_', _TB_VERSION_);
}

/* Initialize error reporting logic */
ErrorHandler::getInstance()->init();

require_once $currentDir . DIRECTORY_SEPARATOR . 'bootstrap.php';

/* Custom config made by users */
if (is_file(_PS_CUSTOM_CONFIG_FILE_)) {
    include_once(_PS_CUSTOM_CONFIG_FILE_);
}

if (_PS_DEBUG_PROFILING_) {
    include_once(_PS_TOOL_DIR_.'profiling/Controller.php');
    include_once(_PS_TOOL_DIR_.'profiling/ObjectModel.php');
    include_once(_PS_TOOL_DIR_.'profiling/Db.php');
    include_once(_PS_TOOL_DIR_.'profiling/Tools.php');
}

if (Tools::convertBytes(ini_get('upload_max_filesize')) < Tools::convertBytes('100M')) {
    ini_set('upload_max_filesize', '100M');
}

if (Tools::isPHPCLI() && isset($argc) && isset($argv)) {
    Tools::argvToGET($argc, $argv);
}

/* Redefine REQUEST_URI if empty (on some webservers...) */
if (!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI'])) {
    if (!isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['SCRIPT_FILENAME'])) {
        $_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_FILENAME'];
    }
    if (isset($_SERVER['SCRIPT_NAME'])) {
        if (basename($_SERVER['SCRIPT_NAME']) == 'index.php' && empty($_SERVER['QUERY_STRING'])) {
            $_SERVER['REQUEST_URI'] = dirname($_SERVER['SCRIPT_NAME']).'/';
        } else {
            $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
            if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
            }
        }
    }
}

/* Trying to redefine HTTP_HOST if empty (on some webservers...) */
if (!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = @getenv('HTTP_HOST');
}

$context = Context::getContext();

/* Initialize the current Shop */
try {
    $context->shop = Shop::initialize();
    $context->theme = new Theme((int)$context->shop->id_theme);
    if ((Tools::isEmpty($theme_name = $context->shop->getTheme()) || !Validate::isLoadedObject($context->theme)) && !defined('_PS_ADMIN_DIR_')) {
        throw new PrestaShopException(Tools::displayError('Current theme unselected. Please check your theme configuration.'));
    }
} catch (PrestaShopException $e) {
    $e->displayMessage();
}
define('_THEME_NAME_', $theme_name);
define('__PS_BASE_URI__', $context->shop->getBaseURI());

/* Include all defines related to base uri and theme name */
require_once($currentDir.'/defines_uri.inc.php');

global $_MODULES;
$_MODULES = [];

define('_PS_PRICE_DISPLAY_PRECISION_', (int) Configuration::get('PS_PRICE_DISPLAY_PRECISION'));
// For retrocompatibility with PS 1.6.1 (which messed up in this area).
define('_PS_PRICE_COMPUTE_PRECISION_', _PS_PRICE_DISPLAY_PRECISION_);

/* Load all languages */
Language::loadLanguages();

/* Loading default country */
$default_country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), Configuration::get('PS_LANG_DEFAULT'));
$context->country = $default_country;

/* It is not safe to rely on the system's timezone settings, and this would generate a PHP Strict Standards notice. */
@date_default_timezone_set(Configuration::get('PS_TIMEZONE'));

/* Set locales */
$locale = strtolower(Configuration::get('PS_LOCALE_LANGUAGE')).'_'.strtoupper(Configuration::get('PS_LOCALE_COUNTRY'));
/* Please do not use LC_ALL here http://www.php.net/manual/fr/function.setlocale.php#25041 */
setlocale(LC_COLLATE, $locale.'.UTF-8', $locale.'.utf8');
setlocale(LC_CTYPE, $locale.'.UTF-8', $locale.'.utf8');
setlocale(LC_TIME, $locale.'.UTF-8', $locale.'.utf8');
setlocale(LC_NUMERIC, 'en_US.UTF-8', 'en_US.utf8');

/* Instantiate cookie */
$cookie_lifetime = defined('_PS_ADMIN_DIR_') ? (int)Configuration::get('PS_COOKIE_LIFETIME_BO') : (int)Configuration::get('PS_COOKIE_LIFETIME_FO');
if ($cookie_lifetime > 0) {
    $cookie_lifetime = time() + (max($cookie_lifetime, 1) * 3600);
}

if (defined('_PS_ADMIN_DIR_')) {
    $cookie = new Cookie('psAdmin', '', $cookie_lifetime);
} else {
    $force_ssl = Configuration::get('PS_SSL_ENABLED');
    if ($context->shop->getGroup()->share_order) {
        $cookie = new Cookie('ps-sg'.$context->shop->getGroup()->id, '', $cookie_lifetime, $context->shop->getUrlsSharedCart(), false, $force_ssl);
    } else {
        $domains = null;
        if ($context->shop->domain != $context->shop->domain_ssl) {
            $domains = [$context->shop->domain_ssl, $context->shop->domain];
        }

        $cookie = new Cookie('ps-s'.$context->shop->id, '', $cookie_lifetime, $domains, false, $force_ssl);
    }
}

$context->cookie = $cookie;

/* Create employee if in BO, customer else */
if (defined('_PS_ADMIN_DIR_')) {
    $employee = new Employee($cookie->id_employee);
    $context->employee = $employee;

    if (Validate::isLoadedObject($employee)) {
        /* Auth on shops are re-cached after employee assignation */
        if ($employee->id_profile != _PS_ADMIN_PROFILE_) {
            Shop::cacheShops(true);
        }
        $cookie->id_lang = (int)$employee->id_lang;
    }
}

/* if the language stored in the cookie is not available language, use default language */
if (isset($cookie->id_lang) && $cookie->id_lang) {
    $language = new Language($cookie->id_lang);
}
if (!isset($language) || !Validate::isLoadedObject($language)) {
    $language = new Language(Configuration::get('PS_LANG_DEFAULT'));
}
$context->language = $language;

/* Get smarty */
require_once($currentDir.'/smarty.config.inc.php');
$context->smarty = $smarty;

if (!defined('_PS_ADMIN_DIR_')) {
    if (isset($cookie->id_customer) && (int)$cookie->id_customer) {
        $customer = new Customer($cookie->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            $context->cookie->delete();
        } else {
            $customer->logged = true;
            if ($customer->id_lang != $context->language->id) {
                $customer->id_lang = $context->language->id;
                $customer->update();
            }
        }
    }

    if (!isset($customer) || !Validate::isLoadedObject($customer)) {
        $customer = new Customer();

        /* Change the default group */
        if (Group::isFeatureActive()) {
            $customer->id_default_group = (int)Configuration::get('PS_UNIDENTIFIED_GROUP');
        }
    }
    $customer->id_guest = $cookie->id_guest;
    $context->customer = $customer;
}

/* Link should also be initialized in the context here for retrocompatibility */
$https_link = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
$context->link = new Link($https_link, $https_link);

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_PAYMENT_', Configuration::get('PS_OS_PAYMENT'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_PREPARATION_', Configuration::get('PS_OS_PREPARATION'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_SHIPPING_', Configuration::get('PS_OS_SHIPPING'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_DELIVERED_', Configuration::get('PS_OS_DELIVERED'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_CANCELED_', Configuration::get('PS_OS_CANCELED'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_REFUND_', Configuration::get('PS_OS_REFUND'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_ERROR_', Configuration::get('PS_OS_ERROR'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_OUTOFSTOCK_', Configuration::get('PS_OS_OUTOFSTOCK'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_OUTOFSTOCK_PAID_', Configuration::get('PS_OS_OUTOFSTOCK_PAID'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_OUTOFSTOCK_UNPAID_', Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_BANKWIRE_', Configuration::get('PS_OS_BANKWIRE'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_PAYPAL_', Configuration::get('PS_OS_PAYPAL'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_WS_PAYMENT_', Configuration::get('PS_OS_WS_PAYMENT'));

/**
 * @deprecated use Configuration::get() instead
 */
define('_PS_OS_COD_VALIDATION_', Configuration::get('PS_OS_COD_VALIDATION'));

if (!defined('_MEDIA_SERVER_1_')) {
    define('_MEDIA_SERVER_1_', Configuration::get('PS_MEDIA_SERVER_1'));
}
if (!defined('_MEDIA_SERVER_2_')) {
    define('_MEDIA_SERVER_2_', Configuration::get('PS_MEDIA_SERVER_2'));
}
if (!defined('_MEDIA_SERVER_3_')) {
    define('_MEDIA_SERVER_3_', Configuration::get('PS_MEDIA_SERVER_3'));
}

// This constant is no longer used anywhere in the core, it exits solely
// for retrocompatibility with prestashop modules
if (!defined('_PS_CACHE_ENABLED_')) {
    define('_PS_CACHE_ENABLED_', Cache::isEnabled());
}

Hook::exec('actionRegisterAutoloader');
Hook::exec('actionRegisterErrorHandlers');
