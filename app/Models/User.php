<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * Identificador (codigo) del rol administrador.
     */
    public const ROLE_ADMIN = 'administrador';

    /**
     * Identificador (codigo) del rol editor.
     */
    public const ROLE_EDITOR = 'editor';

    /**
     * Identificador numérico del rol editor, usado como valor por defecto.
     */
    public const ROL_EDITOR_ID = 2;

    /**
     * Roles (codigos) permitidos en el sistema.
     *
     * @var array<int, string>
     */
    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_EDITOR,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * El campo "role_id" se excluye deliberadamente de la asignación masiva:
     * un registro público, el formulario de perfil o una petición genérica
     * no pueden asignarse un rol. El cambio de rol ocurre únicamente a
     * través del controlador administrativo UsuarioController.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Valores por defecto de los atributos para nuevas instancias.
     *
     * El rol se establece por defecto a "editor" (igual que el valor por
     * defecto de la columna en la base de datos) para que un usuario recién
     * creado tenga el rol disponible en el modelo sin necesidad de recargarlo.
     */
    protected $attributes = [
        'role_id' => self::ROL_EDITOR_ID,
    ];

    /**
     * El rol del usuario.
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'role_id');
    }

    /**
     * Indica si el usuario tiene exactamente el rol indicado (por codigo).
     */
    public function hasRole(string $role): bool
    {
        return $this->rol?->codigo === $role;
    }

    /**
     * Indica si el usuario es administrador.
     */
    public function isAdministrador(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Indica si el usuario es editor.
     */
    public function isEditor(): bool
    {
        return $this->hasRole(self::ROLE_EDITOR);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
}
