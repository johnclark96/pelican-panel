<?php

namespace App\Models;

use App\Exceptions\Service\Allocation\ServerUsingAllocationException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Allocation.
 *
 * @property int $id
 * @property int $node_id
 * @property string $ip
 * @property string|null $ip_alias
 * @property int $port
 * @property int|null $server_id
 * @property string|null $notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $alias
 * @property bool $has_alias
 * @property \App\Models\Server|null $server
 * @property \App\Models\Node $node
 *
 * @method static \Database\Factories\AllocationFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation whereIpAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation whereNodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allocation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Allocation extends Model
{
    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'allocation';

    /**
     * The table associated with the model.
     */
    protected $table = 'allocations';

    /**
     * Fields that are not mass assignable.
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public static array $validationRules = [
        'node_id' => 'required|exists:nodes,id',
        'ip' => 'required|ip',
        'port' => 'required|numeric|between:1024,65535',
        'ip_alias' => 'nullable|string',
        'server_id' => 'nullable|exists:servers,id',
        'notes' => 'nullable|string|max:256',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $allocation) {
            throw_if($allocation->server_id, new ServerUsingAllocationException(trans('exceptions.allocations.server_using')));
        });
    }

    protected function casts(): array
    {
        return [
            'node_id' => 'integer',
            'port' => 'integer',
            'server_id' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return $this->getKeyName();
    }

    /**
     * Accessor to automatically provide the IP alias if defined.
     */
    public function getAliasAttribute(?string $value): string
    {
        return (is_null($this->ip_alias)) ? $this->ip : $this->ip_alias;
    }

    /**
     * Accessor to quickly determine if this allocation has an alias.
     */
    public function getHasAliasAttribute(?string $value): bool
    {
        return !is_null($this->ip_alias);
    }

    public function address(): Attribute
    {
        return Attribute::make(
            get: fn () => "$this->ip:$this->port",
        );
    }

    public function toString(): string
    {
        return $this->address;
    }

    /**
     * Gets information for the server associated with this allocation.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Return the Node model associated with this allocation.
     */
    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
