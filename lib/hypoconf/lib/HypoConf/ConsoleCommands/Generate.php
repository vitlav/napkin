<?php
/**
 * User: NIXin
 * Date: 24.09.2011
 * Time: 00:07
 */

namespace HypoConf\ConsoleCommands;

use Symfony\Component\Console as Console;
use Tools\FileOperation;
use HypoConf\ConfigScopes;
use HypoConf\ConfigScopes\ApplicationsDB;
//use Tools\LogCLI;
//use Tools\StringTools;
//use Tools\ArrayTools;
//use Tools\Tree;
//use HypoConf\ConfigScopes;
//use HypoConf\ConfigScopes\ApplicationsDB;
use HypoConf\Paths;
//use Tools\XFormatterHelper;
//use HypoConf\Commands\Helpers;

class Generate extends Console\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('generateone')
            ->setAliases(array('gen1'))
            ->setDescription('Generates and outputs the config file')
            ->setHelp('Generates and outputs the config file.')
            ->addArgument('application', Console\Input\InputArgument::REQUIRED, 'Which application should we generate the config for')
//            ->addArgument('chain', Console\Input\InputArgument::REQUIRED, 'Configuration chain (eg. nginx/php)');
            ->addArgument('directories', Console\Input\InputArgument::REQUIRED + Console\Input\InputArgument::IS_ARRAY, 'Directories (can be multiple)');
//        $this->addOption('more', 'm', Console\Input\InputOption::VALUE_NONE, 'Tell me more');

    }
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $application = $input->getArgument('application');
        $directories = $input->getArgument('directories');
        $files = array();
        
        //ApplicationsDB::LoadAll();

        $configScopes = ApplicationsDB::LoadApplication($application);

        foreach($directories as $dir)
        {
            $filelist = FileOperation::getAllFilesByExtension(Paths::$db.Paths::$separator.Paths::$defaultGroup.Paths::$separator.$dir, 'yml');

            //if(!isset($filelist) || $filelist !== false)
            if($filelist !== false)
            {
                $files = array_merge($files, $filelist);
            }
            else
            {
                user_error('No such site: '.$dir, E_USER_WARNING);
                //return false;
            }
        }

        $settingsDB = new ConfigScopes\SettingsDB();

        // merging the defaults
        $settingsDB->MergeFromYAML(Paths::$db.Paths::$separator.Paths::$hypoconf.Paths::$separator.Paths::$defaultUser.Paths::$separator.'config.yml', false, true, true); //true for compilation

        // merging the files
        $settingsDB->MergeFromYAMLs($files, 'nginx/server', true, true); //true for compilation

        var_dump($settingsDB->DB);

        ApplicationsDB::LoadConfig(&$settingsDB->DB);

        $parsedFile = $configScopes->parseTemplateRecursively($application);
//        echo PHP_EOL.$parsedFile;
        $output->writeln($parsedFile);
    }

}
