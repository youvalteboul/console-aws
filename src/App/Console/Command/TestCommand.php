<?php

namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;

class TestCommand extends Command {
    protected function configure() {
        $this->setName("test")
             ->setDescription("Sample description for our command named test")
             ->setDefinition(array(
                new InputOption('flag', 'f', InputOption::VALUE_NONE, 'Raise a flag'),
                new InputArgument('activities', InputArgument::IS_ARRAY, 'Space-separated activities to perform', null),
             ))
             ->setHelp(<<<EOT
The <info>test</info> command does things and stuff
EOT
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $helper = $this->getHelper('question');

        $command = "AWS_CONFIG_FILE=/Users/youval/.aws/credentials  aws ec2 describe-instances --filters 'Name=tag:service_type,Values=frontcararec' 'Name=instance-state-name,Values=running'";
        $command = "AWS_CONFIG_FILE=/Users/youval/.aws/credentials  aws ec2 describe-instances --filters 'Name=instance-state-name,Values=running'";
        $command = "AWS_CONFIG_FILE=/Users/youval/.aws/credentials  aws ec2 describe-instances";
        $output_cmd = shell_exec($command);

        $result = json_decode($output_cmd, 1);

        foreach ($result['Reservations'] as $idReservation => $reservation) {

            $Instances = $reservation['Instances'];
            foreach ($Instances as $idInstances => $Instance) {
                // Tags
                foreach ($Instance['Tags'] as $tag) {
                    if ( !isset($filters['tag:service_type'][$tag['Key']]) || !in_array($tag['Value'], $filters['tag:service_type'][$tag['Key']]) ) {
                        $filters['tag:'][$tag['Key']][] = $tag['Value'];
                    }
                }

                // instance-state-name
                foreach ($Instance['Monitoring'] as $key => $value) {
                    if ( !isset($filters['instance-state-name']) || !in_array($value, $filters['instance-state-name']) ) {
                        $filters['instance-state-name'][] = $value;
                    }
                }
                
            }
        }


        $question = new ChoiceQuestion(
            'Choississez un filter',
            array_keys($filters),
            0
        );
        $question->setErrorMessage('Le Filtre %s est invalide.');
        $filter_1 = $helper->ask($input, $output, $question);

        do {
            $choices = !isset($filter_2) ? $filters[$filter_1] : $choices[$filter_2];

            $select = !isset($filter_2) ? $filter_1 : $filter_2;

            $question = new ChoiceQuestion(
                'Choississez une valeur',
                array_values( array_unique($choices) ),
                0
            );
            $question->setErrorMessage('Le Filtre %s est invalide.');
            $filter_2 = $helper->ask($input, $output, $question);


        } while ( substr($select, -1) == ':' );


        print_r($filters);
        exit();

        $choices = [];

        foreach ($result['Reservations'] as $key => $instance) {

            $instanceId = $instance['Instances'][0]['InstanceId'];
            $ipAddress = $instance['Instances'][0]['NetworkInterfaces'][0]['PrivateIpAddress'];

            $tags = [];
            foreach ($instance['Instances'][0]['Tags'] as $key => $value) {
                $tags[$value['Key']] = $value['Value'];
                /*if ( $value['Key'] == 'service_type' && !empty($value['Value']) ) {
                    $name = $value['Value'];
                }*/
            }
            //$choices[] = $name . '[' . $ipAddress . '] (' . $instanceId . ')';
            //$choices[] = $ipAddress . ' (' . $name . ')';
            //$choices[] = $name;
            $name = $tags['service_type'];
            if ( !is_array($choices[$name]) || !in_array($ipAddress, $choices[$name]) ) {
                $choices[$name][] = $ipAddress;
            }
        }

        $choicesKeys = array_keys($choices);


        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select your favorite color (defaults to red)',
            $choicesKeys,
            0
        );
        $question->setErrorMessage('Color %s is invalid.');

        $color = $helper->ask($input, $output, $question);



        $question = new ChoiceQuestion(
            'Please select your favorite color (defaults to red)',
            $choices[$color],
            0
        );
        $question->setErrorMessage('Color %s is invalid.');

        $color = $helper->ask($input, $output, $question);


        $output->writeln('You have just selected: '.$color);

        $output->writeln('You have just selected: ssh root@164.132.198.130');

        $ip = "164.132.198.130";
        //exec("ssh root@$ip");


    }

}
