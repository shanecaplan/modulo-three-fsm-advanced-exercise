# Modulo 3 FSM Exercise (Advanced)

## Core Classes

The core classes can be found in the "src" directory.

#### 1. `FiniteStateMachine` Class
A complete finite state machine.

#### 2. `TransitionFunction` Class
Encapsulates the transition function.

#### 3. `ModMachine` Class
Generates a finite state machine for any provided modulus (**without** using PHP's idiomatic mod operator). The FSM is generated dynamically. Only an integer is provided, representing the modulus.

#### 4. `ModThreeMachine` Class
This extends ModMachine and simply passes the integer 3 to the parent constructor. Included for completion.

## Installation

### 1. Install Dependencies

```
composer install
```

This will install PHPUnit and set up autoloading for the project.

## Quick Start

### Using FiniteStateMachine

```php
$tf = new TransitionFunction();

$tf->addTransition('S0', 'a', 'S2');
$tf->addTransition('S0', 'b', 'S1');
$tf->addTransition('S1', 'a', 'S0');
$tf->addTransition('S1', 'b', 'S1');
$tf->addTransition('S2', 'a', 'S1');
$tf->addTransition('S2', 'b', 'S0');

$fsm = new FiniteStateMachine(
    allowedStates: ['S0', 'S1', 'S2'],
    alphabet: ['a', 'b'],
    initialState: 'S0',
    acceptedStates: ['S0', 'S1'],
    transitionFunction: $tf
);

$fsm->execute('b'); // 'S1'
$fsm->execute('ba'); // 'S0'
$fsm->execute('aab'); // 'S1'
$fsm->execute('aaab'); // 'S1'
```

### Using TransitionFunction

```php
$tf = new TransitionFunction();

$tf->addTransition('S0', 'a', 'S1');
$tf->addTransition('S1', 'b', 'S2');
$tf->addTransition('S2', 'c', 'S0');

$tf->execute('S0', 'a'); // 'S1'
$tf->execute('S1', 'b'); // 'S2'
$tf->execute('S2', 'c'); // 'S0'

$tf->hasTransition('S0', 'a'); // true
$tf->hasTransition('S1', 'b'); // true
$tf->hasTransition('S2', 'c'); // true
$tf->hasTransition('S0', 'b'); // false
$tf->hasTransition('S1', 'c'); // false
$tf->hasTransition('S3', 'z'); // false
```

### Using ModMachine

```php
$modFiveMachine = new ModMachine(5);

$modFiveMachine->execute('110'); // 1
$modFiveMachine->execute('1010'); // 0
$modFiveMachine->execute('1101'); // 3
$modFiveMachine->execute('1110'); // 4
$modFiveMachine->execute('1111'); // 0
```

The index.php file also contains the above ModMachine code.

### Using ModThreeMachine

```php
$modThreeMachine = new ModThreeMachine();

$modThreeMachine->execute('110'); // 0
$modThreeMachine->execute('1010'); // 1
$modThreeMachine->execute('1101'); // 1
$modThreeMachine->execute('1110'); // 2
$modThreeMachine->execute('1111'); // 0
```

The index.php file also contains the above ModThreeMachine code.

## Testing

Unit tests are written in PHPUnit and can be found in the "tests" directory. Some use generated data within data providers (for instance, testing mod 1 to mod 25 over a comprehensive list of binary numbers).

### Running All Tests (Compact)

```
./vendor/bin/phpunit
```

### Running All Tests (Verbose)

```
./vendor/bin/phpunit --testdox
```

## API Documentation

### FiniteStateMachine

#### Constructor

```php
public function __construct (
    array $allowedStates,
    array $alphabet,
    string $initialState,
    array $acceptedStates,
    TransitionFunction $transitionFunction
)
```

#### Methods

- `execute (string $input): string` - Executes using the provided input. Returns the final accepted state.
- `getAllowedStates (): array` - Gets allowed states.
- `getAlphabet (): array` - Gets alphabet.
- `getInitialState (): string` - Gets initial state.
- `getAcceptedStates (): array` - Gets accepted states.
- `getTransitionFunction (): TransitionFunction` - Gets transition function.

### TransitionFunction

#### Methods

- `execute (string $inputState, string $inputSymbol): string` - Executes transition function.
- `addTransition (string $inputState, string $inputSymbol, string $nextState): void` - Adds a transition.
- `hasTransition (string $inputState, string $inputSymbol): bool` - Checks if a transition exists.
- `hasTransitionsForState (string $inputState): bool` - Checks if there are any transitions corresponding to a particular state.
- `getStatesCount (): int` - Gets the number of states that have transitions.
- `getTransitionsCountForState (string $inputState): int` - Gets the number of transitions for a particular state.

### ModMachine

#### Constructor

```php
public function __construct (int $modulus)
```

#### Methods

- `execute (string $binary): int` - Computes the remainder of the binary number.
- `getFiniteStateMachine (): FiniteStateMachine` - Gets the associated finite state machine.

### ModThreeMachine

#### Methods

- Same as `ModMachine`.
