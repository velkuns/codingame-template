<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application;

use Velkuns\Codingame\Core\Game\GameAction;
use Velkuns\Codingame\Core\Game\GameInterface;
use Velkuns\Codingame\Core\IO\Input;
use Velkuns\Codingame\Core\IO\Output;
use Velkuns\Codingame\Core\Utils\Logger;
use Velkuns\Codingame\Core\Utils\Timer;

final class Game implements GameInterface
{
    /** @var GameAction $action */
    private $action;

    /** @var Input $input */
    private $input;

    /** @var Output $output */
    private $output;

    /** @var Timer timer */
    private $timer;

    /** @var array<string|int|float|array<string|int|float>> $data Input lines */
    private $data = [];

    /**
     * Game constructor.
     */
    public function __construct()
    {
        $this->action = new GameAction();
        $this->input  = new Input();
        $this->output = new Output();
        $this->timer  = new Timer(new Logger());
    }

    /**
     * Increase round.
     *
     * @return void
     */
    public function newTurn(): void
    {
        //~ First input
        $this->data[] = $this->input->readString();

        //~ Start global time after reading first information (waiting for opponent turn when have opponent.)
        $this->timer->start();

        //~ Read another line...
        $this->data[] = $this->input->readString();
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
        $this->output->write((string) $this->action);
        $this->timer->display();
    }
}
