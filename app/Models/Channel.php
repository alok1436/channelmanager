<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Channel extends Model
{
    protected $table = 'channel';
    
    protected $primaryKey = 'idchannel';

    public function platform(){
        return $this->hasOne('App\Models\Platform', 'platformid', 'platformid');
    }
}
