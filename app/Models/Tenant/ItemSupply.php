<?php

    namespace App\Models\Tenant;


    use Eloquent;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;


    /**
     * App\Models\Tenant\ItemSet
     *
     * @property int|null   $item_id
     * @property int|null   $individual_item_id
     * @property float|null $quantity
     * @property Item  $individual_item
     * @property Item  $item
     * @property Item  $relation_item
     * @method static Builder|ItemSet newModelQuery()
     * @method static Builder|ItemSet newQuery()
     * @method static Builder|ItemSet query()
     * @mixin ModelTenant
     * @mixin Eloquent
     */
    class ItemSupply extends ModelTenant
    {

        protected $fillable = [
            'item_id',
            'individual_item_id',
            'quantity',
            'modifiable',
            'rounded_up',
            'percentage_decimal',
            'cost_per_unit',
            'cost_total',
        ];

        protected $casts = [
            'quantity' => 'float',
            'cost_per_unit' => 'float',
            'cost_total' => 'float',
        ];

        /**
         * @return BelongsTo
         */
        public function item()
        {
            return $this->belongsTo(Item::class);
        }

        /**
         * @return BelongsTo
         */
        public function individual_item()
        {
            return $this->belongsTo(Item::class, 'individual_item_id');
        }

        /**
         * @return BelongsTo
         */
        public function relation_item()
        {
            return $this->belongsTo(Item::class, 'individual_item_id');
        }

        /**
         * @return int
         */
        public function getItemId()
        {
            return (int)$this->item_id;
        }

        /**
         * @param int $item_id
         *
         * @return ItemSet
         */
        public function setItemId($item_id = 0)
        {
            $this->item_id = (int)$item_id;
            return $this;
        }

        /**
         * @return int
         */
        public function getModifiable()
        {
            return (bool)$this->modifiable;
        }

        /**
         * @param int $modifiable
         *
         * @return
         */
        public function setModifiable($modifiable)
        {
            $this->modifiable = (bool)$modifiable;
            return $this;
        }

        /**
         * @return int
         */
        public function getRoundedUp()
        {
            return (bool)$this->rounded_up;
        }

        /**
         * @param int $rounced_up
         *
         * @return
         */
        public function setRoundedUp($rounded_up)
        {
            $this->rounded_up = (bool)$rounded_up;
            return $this;
        }

        /**
         * @return int
         */
        public function getPercentage()
        {
            return (float)$this->percentage_decimal;
        }

        /**
         * @param int $percentage_decimal
         *
         * @return
         */
        public function setPercentage($percentage_decimal)
        {
            $this->percentage_decimal = (float)$percentage_decimal;
            return $this;
        }


        /**
         * @return int
         */
        public function getIndividualItemId()
        {
            return (int)$this->individual_item_id;
        }

        /**
         * @param int $individual_item_id
         *
         * @return ItemSet
         */
        public function setIndividualItemId($individual_item_id = 0)
        {
            $this->individual_item_id = (int)$individual_item_id;
            return $this;
        }

        /**
         * @return float
         */
        public function getQuantity()
        {
            return (float)$this->quantity;
        }

        /**
         * @param float $quantity
         *
         * @return ItemSet
         */
        public function setQuantity($quantity = 0)
        {
            $this->quantity = (float)$quantity;
            return $this;
        }
        public function getCollectionData(){

            //$itemSupp = Item::find($this->individual_item_id);
            $data = $this->toArray();
            $data['item'] = $this->item;
            //$data['cost'] = $this->individual_item->purchase_unit_price;
            $data['modificable'] = ($this->modifiable)?$this->modifiable:0;
            $data['rounded_up'] = ($this->rounded_up)?$this->rounded_up:0;
            $data['individual_item'] = $this->individual_item;
            $data['individual_item']['lots_group'] = $this->individual_item->lots_group;
            return $data;

    }

    }
