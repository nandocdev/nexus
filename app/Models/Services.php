<?php
namespace App\Models;

use Nexus\Modules\Database\Model;

class Services extends Model {
    protected $table = 'services';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];

    // Define relationships here
    // public function relationshipName()
    // {
    //     return $this->belongsTo(RelationshipModel::class);
    // }
}