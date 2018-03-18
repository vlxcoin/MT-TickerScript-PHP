<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/18
 * Time: 下午6:15
 */

require_once __DIR__ . '/../autoload.php';

use \MT\API\ExchangeModel;
use \MT\Config;

class GenerateMtApikeyCommand
{
    private function configSet($config_file, $section, $key, $value) {
        if (file_exists($config_file)) {
            $config_data = parse_ini_file($config_file, true);
        } else {
            $config_data = [];
        }
        $config_data[$section][$key] = $value;
        $new_content = '';
        foreach ($config_data as $section => $section_content) {
            $section_content = array_map(function($value, $key) {
                return "$key=\"$value\"";
            }, array_values($section_content), array_keys($section_content));
            $section_content = implode("\n", $section_content);
            $new_content .= "[$section]\n$section_content\n";
        }
        file_put_contents($config_file, $new_content);
    }


    public function generate($params)
    {
        $exchangeModel  = new ExchangeModel();

        $res            = $exchangeModel->create($params);
        if (isset($res['code']) && isset($res['message']) ) {
            echo sprintf("generate MyToken api key failed. Code: %s, Message: %s\n", $res['code'], $res['message']);
            return false;
        }

        $this->configSet(Config::getConfigFile(), strtolower($params['name']), 'mt_api_key', $res['token']);

        return true;
    }
}

$longOpts   = [
    "name:",
    "website:",
    "contact:",
    "description::",
    "logo_url::",
];
//todo
$options = getopt("", $longOpts);
if (!isset($options['name']) || !isset($options['website']) || !isset($options['contact'])) {
    echo "name, website, contact is required!\n";
    echo sprintf("Please use command:\nphp %s --name=your_exchange_name --website=your_exchange_website --contact=your_exchange_contact", __FILE__);
    exit(0);
}

$generateMtApikeyCmd = new GenerateMtApikeyCommand();
$generateMtApikeyCmd->generate($options);
