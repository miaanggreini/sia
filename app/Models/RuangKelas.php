<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RuangKelas extends Model {
  protected $table = 'ruang_kelas';
  protected $fillable = ['nama','kapasitas'];
  public function rombel(){ return $this->hasMany(\App\Models\Rombel::class,'ruang_id'); }
}