<?php

namespace Test;
use Provider\StoryProvider;
use Testes\Test\StoryAbstract;

require_once __DIR__ . '/../Provider/StoryProvider.php';

class StoryTest extends StoryAbstract
{
    private $story;
    
    public function setUp()
    {
        $this->story = new StoryProvider;
        $this->story->run();
    }
    
    public function assertions()
    {
        $passed = $this->story->getAssertions()->getPassed();
        $failed = $this->story->getAssertions()->getFailed();
        
        $this->assert(isset($passed[0]) && $passed[0]->getMessage() === 'good');
        $this->assert(isset($failed[0]) && $failed[0]->getMessage() === 'bad');
    }
}