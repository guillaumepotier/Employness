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
            ->addOption('environment', '-env', InputOption::VALUE_OPTIONAL, 'environment')
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
                $app['db']->query("INSERT INTO employness_days SET day = NOW(), participants = 'a:0:{}'");
                echo "The day has been created!\n";
                break;
            case 'ask':
                $day = $app['db']->fetchAssoc("SELECT * FROM employness_days WHERE day = DATE(now())");
                if (false === $day) {
                    die("The current day has not yet been created!\n");
                }

                $app['url_generator']->getContext()->setHost($app['host']);
                $users = $app['user.repository']->findAll();
                foreach ($users as $user) {
                    $url = $app['url_generator']->generate(
                        'give_karma', 
                        array(
                            'email'     => $user['email'],
                            'token'     => sha1($user['id'].$user['token']), 
                            'day_id'    => $day['id'],
                        ),true);

                    $message = \Swift_Message::newInstance()
                            ->setSubject('[Employness] '.$app['translator']->trans('daily_feedback_subject').' ('.$day['day'].')')
                            ->setFrom(array($app['mailer.email'] => 'Employness'))
                            ->setTo(array($user['email']))
                            ->setBody($app['translator']->trans('daily_feedback_body').' <a href="'.$url.'">'.$url.'</a>', 'text/html')
                            ->addPart($app['translator']->trans('daily_feedback_body').' '.$url, 'text/plain');
                    $app['mailer']->send($message);
                    echo "Email sent to ".$user['email']."\n";
                }
                break;
            default:
                return $output->writeln('<error>Invalid action</error>');
        }

        return;
    }
}