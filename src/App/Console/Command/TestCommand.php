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

        //$command = "AWS_CONFIG_FILE=/Users/youval/.aws/credentials  aws ec2 describe-instances";
        //$output_cmd = shell_exec($command);

        $output_cmd = file_get_contents(__DIR__ . '/../../../../data/aws_ec2_describe-instances.json');

        $result = json_decode($output_cmd, 1);


        $__filters = [];

        do {
            $__filterName = '';
            $__filterValue = '';

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
                    $filters['instance-state-name'][] = $Instance['State']['Name'];
                }
            }

            $filter     = false;
            $filter_n2  = false;
            $subfilters = [];
            $__filterName = '';
            $__filterValue = '';

            do {

                
                $break      = false;
                
                if ( !$filter ) {
                    $choices = array_keys($filters);
                    $questionLabel = "Choississez un filtre";
                    $filter_n2 = false;
                }
                elseif ( substr($filter, -1) == ':' ) {
                    $subfilters = $filters[$filter];
                    $choices = array_values( array_keys($subfilters) );
                    $questionLabel = "Choississez un completement de filtre";
                    $filter_n2 = true;
                }
                else {
                    $choices = ($filter_n2) ? $subfilters[$filter] : $filters[$filter];
                    $choices = array_values( array_unique($choices) );
                    $questionLabel = "Choississez une valeur";
                    $break = true;
                }

                $question = new ChoiceQuestion($questionLabel, $choices, 0);
                $question->setErrorMessage('Le Filtre %s est invalide.');
                $filter = $helper->ask($input, $output, $question);


                if ( !$break ) {
                    $__filterName .= $filter;
                }
                else {
                    $__filterValue = $filter;
                }

            } while ( !$break );


            $__filters[] = [
                'Name' => $__filterName, 
                'Value' => $__filterValue
            ];

            $question = new ChoiceQuestion(
                'Voulez-vous ajouter un filter ?',
                ['Oui', 'Non'],
                1
            );
            $question->setErrorMessage('Choix invalid');
            $otherFilter = $helper->ask($input, $output, $question);

        }while ( $otherFilter == 'Oui' );

        $filters = [];

        $output->writeln('Selected filters : ');
        foreach ($__filters as $key => $__filter) {
            $filters[] = 'Name='.$__filter['Name'].',Values='.$__filter['Value'];
            $output->writeln(' - Name='.$__filter['Name'].',Values='.$__filter['Value']);
        }

        $command    = "AWS_CONFIG_FILE=/Users/youval/.aws/credentials  aws ec2 describe-instances --filters " . implode(' ', $filters);
        $output_cmd = shell_exec($command);
        $result     = json_decode($output_cmd, 1);


        $choices = [];

        foreach ($result['Reservations'] as $key => $instance) {
            $instanceId = $instance['Instances'][0]['InstanceId'];
            $ipAddress  = $instance['Instances'][0]['NetworkInterfaces'][0]['PrivateIpAddress'];
            $choices[]  = $ipAddress;
        }

        $question = new ChoiceQuestion(
            'Please select your favorite color (defaults to red)',
            array_values( array_unique($choices) ),
            0
        );
        $question->setErrorMessage('Color %s is invalid.');
        $color = $helper->ask($input, $output, $question);

        //print_r($filters);
    }

}
