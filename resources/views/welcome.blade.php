<?php
$a = [
    'color' => 'red',
    'fruits' => [
        'name1' => 'apple',
        'name2' => 'banana',
        'color' => 'yellow',
    ],
];
// echo $a['fruits']['color'];

// $colors = [
//     'red',
//     'blue',
//     'green',
//     'yellow',
// ];
// foreach ($colors as $key => $value) {
//     # code...
//     print_r(Str::upper($value));
// }

$array = array(
    array('name' => 'Jill'),
    array('name' => 'Barry'),
);

usort($array, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});

$array = array_values($array);

print_r($array);


?>
