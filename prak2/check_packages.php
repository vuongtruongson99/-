<?php
$packages = [
    "a" => ["depends" => [["b"], ["c"], ["z"]], "conflict" => []],
    "b" => ["depends" => [["d"]], "conflict" => []],
    "c" => ["depends" => [["d", "e"], ["f", "g"]], "conflict" => []],
    "d" => ["depends" => [], "conflict" => ["e"]],
    "e" => ["depends" => [], "conflict" => []],
    "f" => ["depends" => [], "conflict" => []],
    "g" => ["depends" => [], "conflict" => []],
    "y" => ["depends" => [["z"]], "conflict" => []],
    "z" => ["depends" => [], "conflict" => []],
];

function depend($x, $ys) {
    $statement = "";
    foreach($ys as $y) {
        $statement = $statement . " " . $y;
    }
    $statement = "-" . $x . " " . $statement;
    return $statement;
}

function conflict($x, $y) {
    $statement = "-" . $x . " " . "-" . $y;
    return $statement;
} 

function build_cnf($packages, $install) {
    $index = [];
    $id = 1;
    $clause = "";
    $cnt = 0;

    foreach($packages as $name => $dc) {
        $index[$name] = $id;
        $id++;
    }

    foreach ($packages as $n => $package) {
        $i = $index[$n];
        $c1 = count($package["depends"]);
        $c2 = count($package["conflict"]);

        if ($c1 > 0) {
            foreach($package["depends"] as $depend) {
                $dp = [];
                foreach($depend as $d) {
                    array_push($dp, $index[$d]);
                }
                $clause = $clause . depend($i, $dp) . " 0" . "\n";
                $cnt++;
            }
        }

        if ($c2 > 0) {
            foreach($package["conflict"] as $conflict) {
                $clause = $clause . conflict($i, $index[$conflict]) . " 0" . "\n";
                $cnt++;
            }
        }
    }

    foreach($install as $inst) {
        $clause = $clause . $index[$inst] . " 0" . "\n";
        $cnt++;
    }

    return "p cnf " . count($packages) . " " . $cnt . "\n" . $clause;
}

$out = build_cnf($packages, ["y"]);

$file_cnf = fopen("packages.cnf", "w") or die("Unable to open file!");
fwrite($file_cnf, $out);
fclose($file_cnf);

$run_cmd = shell_exec("minisat packages.cnf result.txt");

$result = file("result.txt", FILE_IGNORE_NEW_LINES);
if ($result[0] == "SAT") {
    $i = 1;
    $index = [];
    foreach($packages as $name => $value) {
        $index[$i] = $name;
        $i++;
    }

    $pack = explode(" ", $result[1]);
    foreach($pack as $n => $v) {
        $p = intval($v);
        if ($p > 0) {
            print($index[$p] . " ");
        }
    }
}
else {
    print("Cannot SAT");
}

?>