<?php

namespace MBO\RemoteGit\Tests;

use MBO\RemoteGit\ClientFactory;
use MBO\RemoteGit\ClientOptions;

class ClientFactoryTest extends TestCase {

    public function testGetTypes(){
        $clientFactory = ClientFactory::getInstance();
        
        $types = $clientFactory->getTypes();
        $this->assertCount(3,$types);
    }

    public function testInvalidType(){
        $clientFactory = ClientFactory::getInstance();
        $thrown = false;
        try {
            $options = new ClientOptions();
            $options->setType('missing');
            $clientFactory->createGitClient($options);
        }catch(\Exception $e){
            $thrown = true;
        }
        $this->assertTrue($thrown,'exception should be thrown');
    }


}

