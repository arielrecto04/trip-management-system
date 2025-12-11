<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
    ];

    protected $with = [
        'roles',
        'profilePicture'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::deleting(function ($user) {
            if($user->profilePicture) {
                Storage::disk('public')->delete($user->profilePicture->url);
                $user->profilePicture()->delete();
            }


            if($user->driver) {
                foreach($user->driver->complianceDocs as $doc) {
                    foreach($doc->attachments as $attachment) {
                        Storage::disk('public')->delete($attachment->url);
                        $attachment->delete();
                    }

                    $doc->delete();
                }
            }
        });
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function has_driver_role()
    {
        return $this->roles->contains('slug', 'driver');
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function attachable()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function profilePicture()
    {
        return $this->morphOne(Attachment::class, 'attachable')
            ->where('type', 'profile_picture');
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'contact_person');
    }
}
