<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BukuBesar extends Model
{
    protected $table = 'jurnals';
    protected $primaryKey = 'id_jurnal_umum';
    public $timestamps = false;

    public function details()
    {
        return $this->hasMany(JurnalDetail::class, 'id_jurnal_umum');
    }
    public function jurnaldetail()
    {
        // sesuaikan nama kolom FK dan PK dengan punyamu
        return $this->hasMany(JurnalDetail::class, 'id_jurnal_umum', 'id_jurnal_umum');
    }

}