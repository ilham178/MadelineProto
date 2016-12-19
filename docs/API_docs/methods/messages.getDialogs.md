## Method: messages.getDialogs  

### Parameters:

| Name     |    Type       | Required |
|----------|:-------------:|---------:|
|offset\_date|[int](../types/int.md) | Required|
|offset\_id|[int](../types/int.md) | Required|
|offset\_peer|[InputPeer](../types/InputPeer.md) | Required|
|limit|[int](../types/int.md) | Required|


### Return type: [messages\_Dialogs](../types/messages\_Dialogs.md)

### Example:


```
$MadelineProto = new \danog\MadelineProto\API();
if (isset($token)) {
    $this->bot_login($token);
}
if (isset($number)) {
    $sentCode = $MadelineProto->phone_login($number);
    echo 'Enter the code you received: ';
    $code = '';
    for ($x = 0; $x < $sentCode['type']['length']; $x++) {
        $code .= fgetc(STDIN);
    }
    $MadelineProto->complete_phone_login($code);
}

$messages_Dialogs = $MadelineProto->messages->getDialogs(['offset_date' => int, 'offset_id' => int, 'offset_peer' => InputPeer, 'limit' => int, ]);
```