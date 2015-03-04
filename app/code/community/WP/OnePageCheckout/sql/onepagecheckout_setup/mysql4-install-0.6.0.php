<?php

$config = Mage::getModel('onepagecheckout/config_observer');
$config->reinitConfigData();
$websites = Mage::app()->getWebsites();
$list = array('');
foreach ($websites as $website) {
    $list[] = $website->getCode();
}
#Mage::log($list);
foreach ($list as $website) {
    $config->copyOrignCheckoutSettings($website);
    $config->copyOrignSalesSettings($website);
}
$config->reinitConfigData();
