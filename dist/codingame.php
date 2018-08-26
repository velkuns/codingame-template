<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


// ============== Game\Game ==============

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

// ============== Application ==============

/**
 * Class Application
 *
 * @author Romain Cottard
 */
final class Application
{
    /** @var GameInterface $game */
    private $game;

    /** @var bool $hasLoop */
    private $hasLoop;

    /**
     * Application constructor.
     *
     * @param GameInterface $game
     * @param bool $hasLoop
     */
    public function __construct(GameInterface $game, $hasLoop = false)
    {
        $this->game    = $game;
        $this->hasLoop = $hasLoop;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        while (true) {

            try {

                $this->game->newTurn();
                $this->game->runTurn();
                $this->game->endTurn();

            } catch (\Exception $exception) {

                Logger::error($exception->getMessage());
                $this->game->endTurn();
            }

            //~ When no loop, break after first run.
            if (!$this->hasLoop) {
                break;
            }
        }
    }
}

// ============== Collection\Collection ==============

/**
 * Class AbstractCollection
 *
 * @author Romain Cottard
 */
abstract class Collection implements \Iterator, \Countable
{
    /** @var array $collection  */
    protected $collection = [];

    /** @var array $indices  */
    protected $indices = [];

    /** @var int $index */
    protected $index = 0;

    /** @var int $size */
    protected $size = 0;

    /**
     * @param mixed $element
     * @param string|int|null $index
     * @return $this
     */
    public function add($element, $index = null): self
    {
        $this->indices[$this->size] = $index ?? $this->size;
        $this->collection[$this->indices[$this->size]] = $element;

        return $this;
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * @return object
     */
    public function current()
    {
        return $this->collection[$this->index];
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return ($this->index < $this->size);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->size;
    }
}

// ============== Compiler\Compiler ==============

/**
 * Class Compiler
 *
 * @author Romain Cottard
 */
class Compiler
{
    /** @var string  */
    private $copyright = '';

    /** @var bool  */
    private $gameLoop = false;

    /** @var string */
    private $rootDir = '';

    /** @var string[] */
    private $sources = [];

    /** @var string[] */
    private $exclude = [];

    /** @var string */
    private $distributionFile = '';

    /**
     * Compiler constructor.
     *
     * @param string $rootDir
     * @param array $config
     */
    public function __construct(string $rootDir, array $config)
    {
        $this->rootDir          = realpath($rootDir);

        $this->sources          = $config['src']       ?? ['/src', '/vendor/velkuns/codingame-core/src'];
        $this->exclude          = $config['exclude']   ?? ['/vendor/velkuns/codingame-core/src/Compiler'];
        $this->distributionFile = $config['dist']      ?? '/dist/codingame.php';
        $this->copyright        = $config['copyright'] ?? '';
        $this->gameLoop         = $config['gameLoop']  ?? false;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $compiledCode = str_replace('#COMPILED_CODE#', $this->getCompiledCode(), $this->getTemplate());

        $this->write($compiledCode);

        $this->check();
    }

    /**
     * @return string
     */
    private function getCompiledCode(): string
    {
        echo 'Compiling: ... ';
        $compiled = '';

        foreach ($this->sources as $directory) {
            $fullPathname = $this->rootDir . $directory;

            $recursiveDirectoryIterator = new \RecursiveDirectoryIterator($fullPathname);

            foreach (new \RecursiveIteratorIterator($recursiveDirectoryIterator) as $file) {

                if ($file->isDir() || $file->getExtension() !== 'php' || in_array($directory, $this->exclude)) {
                    continue;
                }

                $compiled .= $this->replaceHeader(
                    $this->read($file->getPathname()),
                    $file->getPathname(),
                    $fullPathname
                );
            }
        }

        echo 'done' . PHP_EOL;

        return $compiled;
    }

    /**
     * @param string $content
     * @param string $classFile
     * @param string $fullPathname
     * @return string
     */
    private function replaceHeader(string $content, string $classFile, string $fullPathname): string
    {
        $className = str_replace('/', '\\', trim(str_replace([$fullPathname, '.php'], '', $classFile), '/'));
        $comment   = "\n// ============== ${className} ==============";
        $replace   = [
            '`<\?php`'                                                                                                                                                                                    => '',
            "`\/\*\n \* Copyright \(c\) $this->copyright\n \*\n \* For the full copyright and license information, please view the LICENSE\n \* file that was distributed with this source code\.\n \*\\n*/`m" => $comment,
            "``"                                                                                                                                                                             => '',
            "``"                                                                                                                                                                                   => '',
            "`^\n+$`m"                                                                                                                                                                                    => '',
        ];

        return preg_replace(array_keys($replace), array_values($replace), $content);
    }

    /**
     * @param  string $file
     * @return string
     */
    private function read($file): string
    {
        return file_get_contents($file);
    }

    /**
     * @param  string $content
     * @return void
     */
    private function write($content): void
    {
        echo 'Writing: ... ';
        file_put_contents($this->rootDir . '/' . $this->distributionFile, $content);
        echo 'done' . PHP_EOL;
    }

    /**
     * @return void
     */
    private function check(): void
    {
        echo 'Checking syntax: ... ';
        $result = exec('php -l ' . $this->rootDir . '/' . $this->distributionFile, $content);

        if (substr($result, 0, 16) === 'No syntax errors') {
            echo 'OK' . PHP_EOL;
        } else {
            echo 'FAILED' . PHP_EOL . $result . PHP_EOL;
        }
    }

    /**
     * @return string
     */
    private function getTemplate(): string
    {
        return "

/*
 * Copyright (c) $this->copyright
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

#COMPILED_CODE#

(new Application(new Game(), " . var_export($this->gameLoop, true) . "))->run();
";
    }
}

// ============== Game\GameAction ==============

/**
 * Class Action
 *
 * @author Romain Cottard
 */
class GameAction
{
    /** @var string[] $actions  */
    protected $actions;

    /** @var string $separator */
    protected $separator;

    /** @var mixed $default */
    protected $default;

    /**
     * Action constructor.
     *
     * @param string|array $default
     * @param string $separator
     */
    public function __construct($default = 'PASS', $separator = ';')
    {
        $this->actions   = [];
        $this->separator = $separator;
        $this->default   = $default;
    }

    /**
     * @return $this
     */
    public function reset(): self
    {
        $this->actions = [];

        return $this;
    }

    /**
     * @param string|array $action
     * @param string $separator
     * @return $this
     */
    public function add($action, $separator = ' '): self
    {
        $this->actions[] = is_array($action) ? implode($separator, $action) : $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getAsString(): string
    {
        if (empty($this->actions)) {
            $this->add($this->default);
        }

        return implode($this->separator, $this->actions);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getAsString();
    }
}

// ============== Game\GameInterface ==============

/**
 * Interface GameInterface
 *
 * @author Romain Cottard
 */
interface GameInterface
{
    /**
     * Initialize new turn.
     * Read input should be in this method.
     *
     * @return void
     */
    public function newTurn(): void;

    /**
     * Run the turn.
     * Main actions should be performed in this method.
     *
     * @return void
     */
    public function runTurn(): void;

    /**
     * End of turn.
     * Output should be write in this method.
     *
     * @return void
     */
    public function endTurn(): void;
}

// ============== IO\Input ==============

/**
 * Class Input
 *
 * @author Romain Cottard
 */
final class Input
{
    /**
     * @param string $format
     * @return array
     */
    public static function read($format): array
    {
        return fscanf(STDIN, $format);
    }
}

// ============== IO\Output ==============

/**
 * Class Output
 *
 * @author Romain Cottard
 */
final class Output
{
    /**
     * @param string $string
     * @return void
     */
    public static function write(string $string): void
    {
        echo $string . "\n";
    }
}

// ============== Logger\Logger ==============

/**
 * Class Logger
 *
 * @author Romain Cottard
 */
final class Logger
{
    /**
     * @param string $string
     * @param mixed $context
     * @return void
     */
    public static function debug(string $string, $context = null): void
    {
        static::write(__FUNCTION__, $string, $context);
    }

    /**
     * @param string $string
     * @param mixed $context
     * @return void
     */
    public static function info(string $string, $context = null): void
    {
        static::write(__FUNCTION__, $string, $context);
    }

    /**
     * @param string $string
     * @param mixed $context
     * @return void
     */
    public static function error(string $string, $context = null): void
    {
        static::write(__FUNCTION__, $string, $context);
    }

    /**
     * @param string $string
     * @return void
     */
    public static function log(string $string): void
    {
        error_log($string);
    }

    /**
     * @param $type
     * @param string $string
     * @param mixed $context
     * @return void
     */
    private static function write(string $type, string $string, $context = null): void
    {
        $log = '[' . strtoupper($type) . '] '. $string;

        if (!empty($context)) {
            $log .= "\nContext: " . var_export($context, true) . "\n";
        }

        error_log($log);
    }
}

// ============== Math\Math ==============

/**
 * Class Math
 *
 * @author Romain Cottard
 */
final class Math
{
    /**
     * @param int $n
     * @return int
     */
    public static function factorial(int $n): int
    {
        if ($n <= 0) {
            return 0;
        }

        $value = 1;
        for ($i = 2; $i <= $n; $i++) {
            $value *= $i;
        }

        return $value;
    }

    /**
     * @param int $n
     * @param array $count
     * @return int
     */
    public static function factorialMultiple(int $n, array $count): int
    {
        $factorial = static::factorial($n);

        $divider = 1;
        foreach ($count as $value) {
            $divider *= $value > 2 ? static::factorial($value) : $value;
        }

        return (int) ($factorial / $divider);
    }
}

// ============== Math\Permutation ==============

/**
 * Class Permutation
 *
 * @author Romain Cottard
 */
final class Permutation
{
    /**
     * Permute an array according to the seed.
     *
     * @param array $elements
     * @param int $seed
     * @return array
     */
    public static function permute(array $elements, int $seed): array
    {
        $numberOfElement = count($elements);
        $permutations   = [];

        static::permuteTreeLevel($seed, 1, $numberOfElement, $numberOfElement, $elements, $permutations);

        return $permutations;
    }

    /**
     * @param int $seed
     * @param int $level
     * @param int $maxPermutation
     * @param int $numberOfElement
     * @param array $elements
     * @param array $permutations
     * @return void
     */
    private static function permuteTreeLevel(int $seed, int $level, int $maxPermutation, int $numberOfElement, array &$elements, array &$permutations): void
    {
        if ($numberOfElement === 1) {
            $permutations[] = array_pop($elements);
            return;
        }

        $factorial = Math::factorial($numberOfElement - 1);
        $index     = (int) ($seed / $factorial);

        $permutations[] = $elements[$index];

        //~ Remove element from original indices & reset array indices
        unset($elements[$index]);
        $elements = array_values($elements);
        $numberOfElement--;

        //~ If we are not into max tree level, recurse again.
        self::permuteTreeLevel($seed % $factorial, ($level + 1), $maxPermutation, $numberOfElement, $elements, $permutations);
    }
}

// ============== Utils\Number ==============

/**
 * Class Number
 *
 * @author Romain Cottard
 */
final class Number
{
    /**
     * @param float $number
     * @param int $precision
     * @param string $padChar
     * @return string
     */
    public static function format(float $number, $precision = 4, $padChar = '0'): string
    {
        $number = round($number, $precision);

        if (strpos($number, '.') === false) {
            $number = (string) $number . '.0';
        }

        return str_pad($number, $precision + 2, $padChar);
    }
}

// ============== Utils\Timer ==============

/**
 * Class Timer
 *
 * @author Romain Cottard
 */
final class Timer
{
    /** @var array $timers */
    private static $timers = [];

    /**
     * @param string $name
     * @return void
     */
    public static function start(string $name = 'global'): void
    {
        static::$timers[$name] = -microtime(true);
    }

    /**
     * @param string $name
     * @return float
     */
    public static function get(string $name = 'global'): float
    {
        return (static::$timers[$name] + microtime(true));
    }

    /**
     * @param string $name
     * @param bool $inMillisecond
     * @return void
     */
    public static function display(string $name = 'global', bool $inMillisecond = true): void
    {
        $modifier = 1;
        $unit     = 's';

        if ($inMillisecond) {
            $modifier = 1000;
            $unit     = 'ms';
        }

        Logger::debug('Time[' . $name . ']: ' . round(static::get($name) * $modifier, 4) . $unit);
    }
}


(new Application(new Game(), false))->run();
