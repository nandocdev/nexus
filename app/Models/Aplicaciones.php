<?php
namespace App\Models;

use Nexus\Modules\Database\Model;

class Aplicaciones extends Model {
    protected $table = 'aplicaciones';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];

    // Define relationships here
    public function relationshipName() {
        return $this->belongsTo(RelationshipModel::class);
    }

}
