<?php

namespace App\Services\Pterodactyl\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $table = 'pterodactyl_locations';

    public function inStock()
    {
        if($this->stock == -1 OR $this->stock > 15) {
            return 'In stock';
        }

        if ($this->stock >= 1 && $this->stock <= 15) {
            return $this->stock .' units left';
        }

        return 'Out of stock';
    }

}