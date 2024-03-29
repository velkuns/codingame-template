# codingame-template
Codingame - Template Game / Puzzle

## Requirement
- PHP 7.3.+ (Like on codingame)
- composer

## Download
You can get the last version of this project template, here: [https://github.com/velkuns/codingame-template/releases](Release)
Or just click on this link, to download the latest release:
[Latest `zip`](https://github.com/velkuns/codingame-template/archive/2.0.0.zip)
[Latest `tar.gz`](https://github.com/velkuns/codingame-template/archive/2.0.0.tar.gz)

## Installation
Extract files in the directory of your choice.
And execute `composer` installation of this project to get dependencies.

```bash
~/codingame-template$ composer install
```

> velkuns/codingame-core is always installed (main dependency)

> PHPUnit 7+ is installed (required-dev dependency)


## Code your game / puzzle

You can add your code in `src/` directory.

### Rules
* You `MUST` name you main game class `Game` and `MUST` be implement `GameInterface`
* You `MUST` use full `use My\Namespace\For\Class;` in your classes files (will be removed by the compiler)
* Sometimes, Codingame cannot resolve correctly the dependencies in correct order. So be careful with that.
* Compiler remove header file, according to the given format.


## Compilation

Configuration of compiler (game running in a loop ?, source code to inclure or exclude...) is set in
`config/compiler.json`.

To "compile" classes into one single file for codingame puzzle / game, you need to execute compiler.

```bash
~/codingame-template$ bin/compiler
Compiling: ... done
Checking syntax: ... OK
```

Compiler read source code (according to the config) and put it in one single file (default: dist/codingame.php)
With chrome extensions, this file can be automatically sync on codingame.com
