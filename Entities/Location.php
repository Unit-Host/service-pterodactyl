<?php

namespace App\Services\Pterodactyl\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $name
 * @property mixed $country_code
 * @property mixed $location_id
 * @property mixed $stock
 */
class Location extends Model
{
    use HasFactory;
    protected $table = 'pterodactyl_locations';

    public function inStock(): string
    {
        if($this->stock == -1 OR $this->stock > 15) {
            return 'In stock';
        }

        if ($this->stock >= 1 && $this->stock <= 15) {
            return $this->stock .' units left';
        }

        return __('admin.out_of_stock');
    }

}
