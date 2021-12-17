<?php

namespace App\Models;

use App\Exceptions\AddItemTooEarlyException;
use App\Exceptions\ItemLimitExceededException;
use App\Exceptions\ItemNameAlreadyExistsException;
use App\Exceptions\MailNotSentException;
use App\Services\EmailSenderService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public EmailSenderService $emailSenderService;

    public function __construct(array $attributes = [], EmailSenderService $emailSenderService = null)
    {
        $this->emailSenderService = $emailSenderService ?? new EmailSenderService();
        parent::__construct($attributes);
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

    protected $casts = [
        'birthday' => 'datetime'
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
        if ($lastItem !== null) {
            // Il y a déjà des items dans la db
            $date = Carbon::parse($lastItem->created_at);
            $minutes = $date->diffInMinutes($item->created_at);
            if ($minutes < 30) {
                // Pas le droit
                throw new AddItemTooEarlyException("Veuillez attendre 30 minutes avant de créer une nouvelle tâche.");
            }
        }

        if ($this->items()->where("name" , "=", $item->name)->first() !== null) {
            // Nom pas unique
            throw new ItemNameAlreadyExistsException("Une tâche avec ce nom existe déjà. Choisissez un autre nom.");
        }
        if ($this->items()->count() >= 10) {
            // Trop d'items
            throw new ItemLimitExceededException("La limite d'objets a été atteinte.");
        }

        // Tout est bon normalement

        // Mailing

        if ($this->items()->count() == 7 && !$this->emailSenderService->sendEmail($this->email)) {
            throw new MailNotSentException("Le mail ne s'est pas envoyé.");
        }

        $item->save();
        return true;
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
