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
                $sql = "INSERT INTO employness_days SET day = NOW(), participants = 'a:0:{}'";
                $app['db']->query($sql);
                echo "The day has been created!\n";
                break;
            case 'ask':
                $day = $app['db']->fetchAssoc("SELECT * FROM employness_days WHERE day = DATE(now())");
                if (false === $day) {
                    die("The current day has not yet been created!\n");
                }

                $query = $app['db']->query("SELECT * FROM employness_users ORDER BY id ASC");
                while ($user = $query->fetch()) {
                    $url = $app['url_generator']->generate(
                        'give_karma', 
                        array(
                            'email'     => $user['email'],
                            'token'     => sha1($user['id'].$user['token']), 
                            'day_id'    => $day['id'],
                        ),true);
                    $message = \Swift_Message::newInstance()
                            ->setSubject('[Employness] Your Daily Feedback! ('.$day['day'].')')
                            ->setFrom(array('noreply@guillaumepotier.com'))
                            ->setTo(array($user['email']))
                            ->setBody('<a href="'.$url.'">'.$url.'</a>', 'text/html')
                            ->addPart($url, 'text/plain');
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