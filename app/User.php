<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Exception;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email',
        'avatar', 'provider_id', 'provider',
        'access_token'    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'provider', 'provider_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    /*protected $casts = [
        'email_verified_at' => 'datetime',
    ];*/

    public function applications()
    {
        return $this->belongsToMany('App\Models\Application');
    }

    public static function getAvatar($accessToken){
        //https://graph.microsoft.com/v1.0/me/photo/$value
        try{
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://graph.microsoft.com/v1.0/me/photos/48x48/$value',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => array(
                    "Accept: image/*",
                    "Authorization: Bearer $accessToken"
                ),
            ));
            $image_type = null;
            curl_setopt(
                $curl,
                CURLOPT_HEADERFUNCTION,
                function ($curl, $header) use (&$image_type) {
                    $len = strlen($header);
                    $header = explode(':', $header, 2);
                    if (count($header) < 2) // ignore invalid headers
                        return $len;
            
                    if (strtolower(trim($header[0])) == 'content-type') {
                        $image_type = trim($header[1]);
                    }
                    return $len;
                }
            );
            
            $response = curl_exec($curl);
            curl_close($curl);
            if ($image_type != '') {
                $base64 = 'data:' . $image_type . ';base64,' . base64_encode($response);
            } else {
                $base64 = null;
            }
            return $base64;
        } catch (\Exception $e) {
            return null;
        }
    }
}
