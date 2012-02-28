<?php

namespace Test;
use Provider\StoryProvider;
use Testes\Test\Type\StoryAbstract;

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
        $ass = $this->story->getAssertions();
        $this->assert($ass[0]->passed());
        $this->assert($ass[0]->getMessage() === 'good');
        $this->assert($ass[1]->failed());
        $this->assert($ass[1]->getMessage() === 'bad');
    }
}