<?php
//make by @Alipbot

$intro ="\033[1;34m   ________                __    
  / ____/ /___  __  ______/ /____
 / /   / / __ \/ / / / __  / ___/
/ /___/ / /_/ / /_/ / /_/ (__  ) 
\____/_/\____/\__,_/\__,_/____/   by @Alipbot
                                 \033[0m\n\n";
echo $intro;


if (file_exists('token.txt')) {
    $token = trim(file_get_contents('token.txt'));
} else {
    $token = readline("Enter Your Token: ");
    $save = readline("Do you want to save this token to token.txt? (y/n): ");
    if (strtolower(trim($save)) == 'y') {
        file_put_contents('token.txt', $token);
        echo "Token saved to token.txt\n\n";
    }
}
echo "Catcher Types:\n\033[32m1. Basic Catcher(6s) \033[0m\n\033[34m2. Advanced Catcher(30s)\033[0m\n\033[33m3. Superior Catcher(90s)\033[0m\n";
$catcher = readline("Choose a Catcher Type (1/2/3): ");
echo "\n";

switch ($catcher) {
    case '1':
        $time = 6;
        $name = "\033[32m Basic Catcher\033[0m";
        break;
    case '2':
        $time = 30;
        $name = "\033[34m Advanced Catcher\033[0m"; 
        break;
    case '3':
        $time = 90;
        $name = "\033[33m Superior Catcher\033[0m";
        break;
    default:
        die("Invalid choice. Exiting.\n");
}


function postData($url, $param, $token) {

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($param),
        CURLOPT_HTTPHEADER => [
            "x-token: $token",
            "Accept: */*",
            "User-Agent: Mozilla/5.0"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch) . "\n";
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return $response;
}


while (true){

// ━━━━━━━━━━━━━━━━━━BUY━━━━━━━━━━━━━━━━━━━━━━━\\


echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
// Step 1: Buy 3 captures
$captures = [];
for ($i = 0; $i < 3; $i++) {
    $buyResponse = postData("https://app.web.moontontech.com/actgateway/clouds/shop/buy", ['net_type' => $catcher], $token);
    if ($buyResponse) {
        echo "Success buy Catcher $name ".($i+1)."\n";
        $captures[] = true; // placeholder for each capture
    } else {
        echo "Failed to buy Catcher $name ".($i+1)."\n";
    }
    sleep(1); // small delay between buys
}
echo "\n";

// ━━━━━━━━━━━━━━━━━━━START CAPTURES━━━━━━━━━━━━━━━━━━━━\\


// Step 2: Start 3 captures
$netSlotIds = [];
for ($i = 0; $i < count($captures); $i++) {
    $startResponse = postData("https://app.web.moontontech.com/actgateway/clouds/capture/start", ['net_type' => $catcher], $token);
    if ($startResponse) {
        $data = json_decode($startResponse, true);
        if (isset($data['data']['net_slot'][$i]['id'])) {
            $id = $data['data']['net_slot'][$i]['id'];
            $netSlotIds[] = $id;
            echo "Capture ".($i+1)." started, ID: $id\n";
        } else {
            echo "\033[31mNet slot ID not found . Please Make Sure You Clear The Net Slot \033[0m ".($i+1)."\n";
        }
    }
    sleep(1); // small delay between starts
}
echo "\n";

// ━━━━━━━━━━━━━━━ TIMER ━━━━━━━━━━━━━━━━━━━━\\
for ($i = $time; $i > -1; $i--) {
    echo "\033[36m\033[2KWaiting $i seconds ⏰\033[0m\r";
    sleep(1);
}
echo "\n\n";

// ━━━━━━━━━━━━━━━━━━━━RECEIVE AND SELL CAPTURES━━━━━━━━━━━━━━━━━━━━━\\

// Step 3: Receive and sell each capture 
foreach ($netSlotIds as $index => $id) {
sleep(1); // small delay before receiving
    // Receive capture
    $receiveResponse = postData("https://app.web.moontontech.com/actgateway/clouds/capture/receive", ['id' => $id], $token);
    if (!$receiveResponse) {
        echo "Failed to receive capture ".($index+1)."\n";
        continue;
    }

    $receiveData = json_decode($receiveResponse, true);
    if (!isset($receiveData['data']['illustrated']['id'])) {
        echo "Failed get ID cloud ".($index+1)."\n";
        continue;
    }

    $idawan = $receiveData['data']['illustrated']['id'];
    $namawan = $receiveData['data']['illustrated']['name'] ?? 'Unknown';
    $hargaawan = $receiveData['data']['illustrated']['sell_price'] ?? 'N/A';
    echo "Capture ".($index+1)." received\n";
    echo "\033[36mID cloud: \033[0m$idawan \n\033[32mName: \033[0m$namawan\n\033[33mSell Price: \033[32m$hargaawan\033[0m\n";

    // Sell capture
    $sellResponse = postData("https://app.web.moontontech.com/actgateway/clouds/inventory/illustrated/sell", [
        'id' => $idawan,
        'num' => 1
    ], $token);
    if ($sellResponse) {
        $sellData = json_decode($sellResponse, true);
        $coin = $sellData['data']['user_info']['total_coin'] ?? 'N/A';
        echo "\033[33mTotal Coins: $coin \033[0m\n\n";
    }
    sleep(2); // small delay between sells
}
echo "\n";

}