<?php

namespace App\BotExtensionModels;

use App\Products;
use Illuminate\Support\Facades\Log;

class Cart
{
    private $redis_cart;
    private $agent;

    public function __construct($redis_cart, $agent) {
        $this->redis_cart = $redis_cart;
        $this->agent = $agent;
    }

    private function getEntityModel($entity, $attributes, $quantity) {

        $img_url = null;
        if ($entity->is_image)
            $img_url = config('agent.base_url') . '/uploads/' . $entity->is_image;
        else if ($entity->image_link)
            $img_url = $entity->image_link;

        if ($entity->stock >= 0 && $entity->stock < $quantity) $quantity = $entity->stock;

        $data = [
            'id' => $entity->id, // Entity id
            'name' => $entity->name, // Name of the entity
            'img_url' => $img_url, // Entity image url
            'unit_price' => (float)$entity->offer_price, // Entity unit price
            'quantity' => $quantity, // Number of entities
            'total_price' => (float)$quantity * $entity->offer_price, // Unit price multiplied by quantity
            'attributes' => $attributes,
            'entity_code' => $entity->code, // Merchant given entity identification code
            'available_quantity' => $entity->stock <= -1 ? 100 : min(100, $entity->stock), // Number of sellable entities, null (depends on is_available)/ >= 0 (is_available = true)
            'is_available' => ($entity->stock > 0 || $entity->stock <= -1) ? true : false // Is entity available, true / false
        ];

        return $data;
    }

    public function cartData() {
        $order_number = $this->redis_cart['order_id'];
        $cart_items = $this->redis_cart['entities'];
        $total_quantity = 0;
        $total_price = 0.0;
        $entities = [];
        $cart_name = $this->agent->fb_page_name;
        $agent_name = $this->agent->agent_name;

        foreach ($cart_items as $item) {
            $entity_id = $item['id'];
            $attributes = $item['attributes'];

            $quantity = $item['quantity'];
            $entity = Products::find($entity_id);

            if (isset($entity)) {
                $entity_model = $this->getEntityModel($entity, $attributes, $quantity);
                $entities[] = $entity_model;

                $total_quantity += $entity_model['quantity'];
                $total_price += (float)$entity_model['total_price'];
            }
        }


        $data = [
            'total_price' => $total_price, // Sum of prices of all entities in cart
            'order_number' => $order_number, // Unique order id created by system
            'total_quantity' => $total_quantity, // Total number of entities in cart
            'entities' => $entities,
            'max_order_quantity' => 100, // Maximum quantity of an entity purchaseable at a time
            'is_checkedout' => false, // Is cart checked out and order confirmed
            'is_cart_editable' => true, // Is entities in cart updateable
            'order_status' => 0, // null / business stated order status

            'page_title' => 'Usha Bag', // Cart view Title, e.g => Usha Bag or Usha Order
            'cart_name' => $cart_name, // Cart name given by us
            'generic_entity_name' => 'Product', // Generic entity name given by business. e.g => Product, Rooms
            'currency' => 'BDT', // Currency name, e.g => BDT, USD
            'currency_sign' => '৳', // Currency sign, e.g => ৳, $

            'business_name' => $agent_name, // Name of the business or merchant
            'external_business_id' => $this->agent->fb_page_id, // external platform scoped business id
            'business_img_url' => $this->agent->image_path // business image url / null
        ];

        return $data;
    }
}
