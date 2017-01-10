<?php

namespace App\Parser\DescribeInstances;

class DescribeInstancesParser {

    public function __construct () {
        $ec2 = new Aws\Ec2\Ec2Client([
            /*
            'version' => '2014-10-01',
            'region'  => 'us-west-2',
            'profile' => 'production'
            */
            'version'     => 'latest',
            'region'      => 'us-west-2',
            'credentials' => [
                'key'    => 'my-access-key-id',
                'secret' => 'my-secret-access-key',
            ],
        ]);
    }
}