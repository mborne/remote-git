<?php

namespace MBO\RemoteGit\Tests;

use MBO\RemoteGit\ClientFactory;

class ClientFactoryTest extends TestCase {

    public function testGetTypes(){
        $clientFactory = ClientFactory::getInstance();
        
        $types = $clientFactory->getTypes();
        $this->assertCount(3,$types);
    }


}

