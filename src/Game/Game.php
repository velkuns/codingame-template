<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velkuns\Codingame\Template\Game;

use Velkuns\Codingame\Core\Game\GameAction;
use Velkuns\Codingame\Core\Game\GameInterface;
use Velkuns\Codingame\Core\IO\Input;
use Velkuns\Codingame\Core\IO\Output;
use Velkuns\Codingame\Core\Utils\Timer;

final class Game implements GameInterface
{
    /** @var GameAction $action */
    private $action;

    /** @var array $input Input lines */
    private $input = [];

    /**
     * Game constructor.
     */
    public function __construct()
    {
        $this->action = new GameAction();
    }

    /**
     * Increase round.
     *
     * @return void
     */
    public function newTurn(): void
    {
        //~ First input
        $this->input[] = Input::read('%s');

        //~ Start global time after reading first information (waiting for opponent turn when have opponent.)
        Timer::start();

        //~ Read another line...
        $this->input = Input::read('%s');
    }

    /**
     * @return void
     */
    public function runTurn(): void
    {
        //~ Do something here.

        //~ Add game action for output
        $this->action->add('SOMETHING');
    }

    /**
     * @return void
     */
    public function endTurn(): void
    {
        Output::write((string) $this->action);
        Timer::display();
    }
}
