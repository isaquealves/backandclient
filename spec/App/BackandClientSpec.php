<?php

namespace spec\App;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BackandClientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('App\BackandClient');
    }
}
