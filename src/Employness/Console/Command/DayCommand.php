<?php

namespace Employness\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DayCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('day')
            ->setDescription('create a new day for notation')
            ->setHelp('You can create a new day with: <info>day create</info>')
            ->addArgument('action', InputArgument::REQUIRED, 'create/ask')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $requireOption = function($name) use($input) {
            if (null === $input->getOption($name)) {
                throw new \InvalidArgumentException(sprintf('You must provide the "%s" option', $name));
            }
        };

        $app = $this->getApp();
        switch($input->getArgument('action')) {
            case 'create':
                // TODO: use proper dql..
                $sql = "INSERT INTO employness_days SET day = NOW()";
                $app['db']->query($sql);
                break;
            case 'ask':
                $day = $app['db']->query("SELECT * FROM employness_days WHERE day = DATE(now())");
                if (false === $day) {
                    die('The current day has not yet been created!');
                }

                $query = $app['db']->query("SELECT * FROM employness_users ORDER BY id ASC");
                while ($user = $query->fetch()) {
                    $body = '<a href="'.$app['url_generator']->generate(
                        'give_karma', 
                        array(
                            'token' => $user['token'], 
                            'day' => $day['id']
                        )).'"></a>';
                    $message = \Swift_Message::newInstance()
                            ->setSubject('[Employness] Your Daily Feedback!')
                            ->setFrom(array('noreply@guillaumepotier.com'))
                            ->setTo(array($user['email']))
                            ->setBody($body);
                    $app['mailer']->send($message);
                }

                break;
            default:
                return $output->writeln('<error>Invalid action</error>');
        }

        return;
    }
}