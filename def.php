<?php
// simple path matting for Containers Vagrant etc
$filePath = str_replace('/home/nicholas/project-1/', '/var/www/', $argv[1]);

// remeber humans count from 1, array offsets count from 0
$lineToFind = $argv[2];
$lineToFind--;
$wordToFind = $argv[3];

// not bothered reading line by line, for now just get the whole file
$code = file_get_contents($filePath);

$methodStartAts = [];
$lines = explode(PHP_EOL, $code);

// count from 0
//$lineToFind = 81;
//$wordToFind = '$currentUser';

/*
function multiExplode($delimiters, $data) {
	$MakeReady = str_replace($delimiters, $delimiters[0], $data);
	$Return    = explode($delimiters[0], $MakeReady);
	return $Return;
}*/

function testIsArgLine(string $line): bool {
    if (strpos($line, '(') === false) {
        return false;
    }
    
    if (strpos($line, 'function') === false) {
        return false;
    }

    $beforeArgs = explode('(', $line);
    $beforeArgs = $beforeArgs[0];

    if (strpos($beforeArgs, '$') !== false ||
        strpos($beforeArgs, '=') !== false) {
        return false;
    }

    return true;
}

foreach ($lines as $i => $line) {
    if (testIsArgLine($line)) {
        $methodStartAts[] = $i;
    }
}

$methodStartAts[] = count($lines);

function findDefInMethod($lines, $start, $end, $wordToFind) {
    $linesInFunction = array_slice($lines, $start, $end - $start);
    //$block = implode(PHP_EOL, $linesInFunction);
    
    if (strpos($linesInFunction[0], $wordToFind . ',') !== false) {
        return ($start + 1) . '    ' . trim($linesInFunction[0]);
    }
    if (strpos($linesInFunction[0], $wordToFind . ')') !== false) {
        return ($start + 1) . '    ' . trim($linesInFunction[0]);
    }
    if (strpos($linesInFunction[0], $wordToFind . ' ') !== false) {
        return ($start + 1) . '    ' . trim($linesInFunction[0]);
    }
    if (strpos($linesInFunction[0], $wordToFind . '=') !== false) {
        return ($start + 1) . '    ' . trim($linesInFunction[0]);
    }
    
    foreach ($linesInFunction as $i => $line) {
        if (strpos($line, $wordToFind . ' = ') !== false) {
            return ($start + $i + 1) . '    ' . trim($line);
        }
        if (strpos($line, $wordToFind . '  = ') !== false) {
            return ($start + $i + 1) . '    ' . trim($line);
        }
        if (strpos($line, $wordToFind . '   = ') !== false) {
            return ($start + $i + 1) . '    ' . trim($line);
        }
    }
    return '';
}

foreach ($methodStartAts as $methodStartAtI => $methodStartAt) {
    if ($lineToFind < $methodStartAt) {
        $methodStartAtILast = $methodStartAts[$methodStartAtI - 1];
        echo findDefInMethod(
            $lines,
            $methodStartAtILast,
            $methodStartAt,
            $wordToFind);
        die;
    }
}