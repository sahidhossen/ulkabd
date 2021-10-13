<?php

namespace App;

class ApiaiActionSlots {
    public $index, $count;
    public $slots;

    public function __construct()
    {
        $this->index = 0;
        $this->slots = [];
    }

    public function prepareSlots($category, $productId) {
        if ($category !== null && $productId !== null) {

            $product = Products::find($productId);

            // Product
            $this->slots[] = [
                'key' => $category->apiai_entity_name,
                'value' => $productId,
                'params' => []
            ];
            $this->count += 1;

            // Attributes
            $attStr = $category->required_attributes;
            $pAttributes = json_decode($product->product_attributes, true);

            if (isset($attStr) && strlen($attStr) > 0) {
                $attArray = explode(',', $attStr);

                foreach($attArray as $att) {
                    $key = mb_strtolower($att);
                    $key = preg_replace('/\s+/', '-', $key);
                    $key = preg_replace('/-+/', '-', $key);
                    $slot = [
                        'name' => $att,
                        'key' => $key,
                        'value' => '',
                    ];

                    $params = [
//                        [
//                            'title' => 'Cancel',
//                            'payload' => 'Cancel'
//                        ]
                    ];
                    if (isset($pAttributes[$att])) {
                        if ($pAttributes[$att][0] != '?') {
                            $pAttValues = explode(',', $pAttributes[$att]);
                            foreach($pAttValues as $pAttValue) {
                                $params[] = [
                                    'title' => $pAttValue,
                                    'payload' => $pAttValue
                                ];
                            }
                        }
                    }
                    else {
                        $params[] = [
                            'title' => 'N/A',
                            'payload' => 'N/A'
                        ];
                    }
                    $slot['params'] = $params;

                    $this->slots[] = $slot;
                    $this->count += 1;
                }
            }

//            // add quantity
//            $this->slots[] = [
//                'key' => 'quantity',
//                'value' => '',
//                'params' => [
////                    [
////                        'title' => 'Cancel',
////                        'payload' => 'Cancel'
////                    ]
//                ]
//            ];
//            $this->count += 1;
//
//            // add phone
//            $this->slots[] = [
//                'key' => 'phone',
//                'value' => '',
//                'params' => [
////                    [
////                        'title' => 'Cancel',
////                        'payload' => 'Cancel'
////                    ]
//                ]
//            ];
//            $this->count += 1;
//
//            // add address
//            $this->slots[] = [
//                'key' => 'address',
//                'value' => '',
//                'params' => [
////                    [
////                        'title' => 'Cancel',
////                        'payload' => 'Cancel'
////                    ]
//                ]
//            ];
//            $this->count += 1;
//
//            // add confirmation
//            $this->slots[] = [
//                'key' => 'confirmation',
//                'value' => '',
//                'params' => [
//                    [
//                        'title' => 'Cancel',
//                        'payload' => 'Cancel'
//                    ],
//                    [
//                        'title' => 'Yes, I confirm',
//                        'payload' => 'Done'
//                    ]
//                ]
//            ];
//            $this->count += 1;
        }

        return null;
    }

}
