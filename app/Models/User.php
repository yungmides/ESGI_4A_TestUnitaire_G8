<?php

namespace App\Models;

use App\Services\EmailSenderService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    private EmailSenderService $emailSenderService;

    public function __construct(array $attributes = [], EmailSenderService $emailSenderService = null)
    {
        parent::__construct($attributes);

        $this->emailSenderService = $emailSenderService ?? new EmailSenderService();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'birthday',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];

    public function isValid(): bool {
        return !empty($this->email)
            && filter_var($this->email, FILTER_VALIDATE_EMAIL)
            && !empty($this->firstname)
            && !empty($this->lastname)
            && strlen($this->password) >= 8
            && strlen($this->password) < 40
            && !is_null($this->birthday)
            && $this->birthday->addYears(13)->isBefore(Carbon::now());
    }

    /**
     * @throws Exception
     */
    public function add(Item $item) : bool {
        $lastItem = $this->items()->latest("created_at")->first();
        $date = Carbon::parse($lastItem->created_at);
        $minutes = $date->diffInMinutes(Carbon::now());

        if ($minutes < 30) {
            // Pas le droit
            throw new Exception("Veuillez attendre 30 minutes avant de créer une nouvelle tâche.");
        }
        if ($this->items()->where("name" , "=", $item->name)->first() !== null) {
            throw new Exception("Une tâche avec ce nom existe déjà. Choisissez un autre nom.");
        }
        if ($this->items()->count() >= 10) {
            throw new Exception("La limite d'objets a été atteinte.");
        }

        // Tout est bon normalement

        // Mailing
        if ($this->items()->count() == 7 && !$this->emailSenderService->sendEmail($this->email)) {
            throw new Exception("Le mail ne s'est pas envoyé.");
        }

        $item->save();
        return true;
    }

    public function items() {
        return $this->hasMany(Item::class);
    }
}
