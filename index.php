<?php
require_once __DIR__.'/vendor/autoload.php';

use FSM\ModMachine;
use FSM\ModThreeMachine;

$modThreeMachine = new ModThreeMachine();

echo "Modulo 3:";
echo "\n";
echo "  '110' => ".$modThreeMachine->execute('110');
echo "\n";
echo "  '1010' => ".$modThreeMachine->execute('1010');
echo "\n";
echo "  '1101' => ".$modThreeMachine->execute('1101');
echo "\n";
echo "  '1110' => ".$modThreeMachine->execute('1110');
echo "\n";
echo "  '1111' => ".$modThreeMachine->execute('1111');
echo "\n\n";

$modFiveMachine = new ModMachine(5);

echo "Modulo 5:";
echo "\n";
echo "  '110' => ".$modFiveMachine->execute('110');
echo "\n";
echo "  '1010' => ".$modFiveMachine->execute('1010');
echo "\n";
echo "  '1101' => ".$modFiveMachine->execute('1101');
echo "\n";
echo "  '1110' => ".$modFiveMachine->execute('1110');
echo "\n";
echo "  '1111' => ".$modFiveMachine->execute('1111');
