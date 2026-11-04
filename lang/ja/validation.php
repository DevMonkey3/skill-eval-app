<?php

// このアプリで使っているルールだけ日本語化している。ここに無いものは英語で表示される
return [
    'before_or_equal' => ':attributeは:date以前の日付を指定してください。',
    'between' => [
        'numeric' => ':attributeは:min〜:maxの間で指定してください。',
    ],
    'date' => ':attributeは正しい日付で入力してください。',
    'email' => ':attributeは正しいメールアドレスの形式で入力してください。',
    'exists' => '選択された:attributeは存在しません。',
    'in' => '選択された:attributeは正しくありません。',
    'integer' => ':attributeは整数で指定してください。',
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください。',
    ],
    'regex' => ':attributeの形式が正しくありません。',
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列で入力してください。',
    'unique' => 'この:attributeはすでに使われています。',

    'values' => [
        'hired_on' => [
            'today' => '今日',
        ],
    ],
];