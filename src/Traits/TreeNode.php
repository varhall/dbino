<?php

namespace Varhall\Dbino\Traits;

use Nette\Caching\Cache;
use Nette\Caching\Storages\MemoryStorage;
use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Dbino;
use Varhall\Dbino\Events\InsertArgs;
use Varhall\Dbino\Scopes\ClosureScope;
use Varhall\Utilino\Utils\Reflection;

trait TreeNode
{
    private static $cache = null;

    /// STATIC METHODS

    public static function roots()
    {
        $parent = static::instance()->treeColumns()->parent;
        return static::all()->where($parent, null);
    }

    protected static function paths($column, $separator = ',')
    {
        $config = static::instance();

        $table = $config->table();
        $left = $config->treeColumns()->left;
        $right = $config->treeColumns()->right;

        $hasSoftDelete = Reflection::hasTrait(static::class, SoftDeletes::class);
        $sdColumn = $hasSoftDelete ? $config->softDeleteColumn() : null;

        $softDeletes = (object) [
            'where'     => $hasSoftDelete ? "WHERE t0.{$sdColumn} IS null" : '',
            'select'    => $hasSoftDelete ? "AND t2.{$sdColumn} IS null" : '',
        ];

        return Dbino::instance()->explorer()->query("SELECT t0.id AS id, 
                                (SELECT GROUP_CONCAT(t2.{$column} ORDER BY t2.{$left} SEPARATOR '{$separator}')
                                    FROM {$table} t2 
                                    WHERE t2.{$left} <= t0.{$left} AND t2.{$right} >= t0.{$right} {$softDeletes->select}
                                ) AS path,
                                (SELECT COUNT(t2.id)
                                    FROM {$table} t2 
                                    WHERE t2.{$left} < t0.{$left} AND t2.{$right} > t0.{$right} {$softDeletes->select}
                                ) AS depth
                            FROM {$table} t0
                            {$softDeletes->where}
                            GROUP BY t0.id;");
    }

    public static function orphans()
    {
        $data = static::all()->asArray();

        $items = static::traverse($data);
        return static::where('id NOT', $items);
    }

    private static function traverse($data, $left = 0, $right = null)
    {
        $tree = [];

        foreach ($data as $item) {
            $l = $item->{$item->treeColumns()->left};
            $r = $item->{$item->treeColumns()->right};

            if ($l === $left + 1 && (is_null($right) || $r < $right)) {
                $tree = array_merge($tree, [$item->id], static::traverse(array_filter($data, function ($x) use ($l, $r) {
                    return $x->{$x->treeColumns()->left} > $l && $x->{$x->treeColumns()->right} < $r;
                }), $l, $r));

                $left = $r;
            }
        }

        return $tree;
    }


    /// METHODS

    public function moveToEnd()
    {
        $max = static::all()->max($this->treeColumns()->left) ?? 0;

        $this->{$this->treeColumns()->left} = $max + 1;
        $this->{$this->treeColumns()->right} = $max + 2;
        $this->{$this->treeColumns()->parent} = null;

        return $this->save();
    }


    /// DYNAMIC PROPERTIES

    public function parent_node()
    {
        return $this->belongsTo(static::class, $this->treeColumns()->parent);
    }

    public function root_node()
    {
        return $this->ancestors(true)->first();
    }

    public function children()
    {
        return $this->hasMany(static::class, $this->treeColumns()->parent)->order($this->treeColumns()->left);
    }

    public function ancestors($includeSelf = false)
    {
        $left = $this->treeColumns()->left;
        $right = $this->treeColumns()->right;

        return static::all()
            ->where("{$left} <" . ($includeSelf ? '=' : '') . ' ?', $this->{$left})
            ->where("{$right} >" . ($includeSelf ? '=' : '') . ' ?', $this->{$right});
    }

    public function descendants($includeSelf = false)
    {
        $left = $this->treeColumns()->left;
        $right = $this->treeColumns()->right;

        return static::all()
            ->where("{$left} >" . ($includeSelf ? '=' : '') . ' ?', $this->{$left})
            ->where("{$right} <" . ($includeSelf ? '=' : '') . ' ?', $this->{$right});
    }

    public function path($separator = ' > ')
    {
        if (!static::$cache)
            static::$cache = new Cache(new MemoryStorage());

        $key = 'tree-paths-' . (new \ReflectionClass($this))->getShortName();
        $result = static::$cache->load($key, function() use ($separator) {
            return static::paths('name', $separator)->fetchPairs('id');
        });

        return $result[$this->getPrimary()]->path;
    }



    /// PRIVATE & PROTECTED METHODS

    protected function fillColumns()
    {
        $config = static::instance();

        $left = $config->treeColumns()->left;
        $right = $config->treeColumns()->right;

        if (isset($model->$left) || isset($model->$right))
            return;

        $max = $this->maxIndex($config);

        $this->$left = $max + 1;
        $this->$right = $max + 2;
    }

    protected function maxIndex()
    {
        $max = static::all()->max($this->treeColumns()->right);
        return $max ? $max : 0;
    }



    /// CONFIGURATION

    protected function initializeTreeNode()
    {
        // order
        $left = $this->treeColumns()->left;
        $this->addScope(new ClosureScope(fn(Collection $collection) => $collection->order($left)), 'tree-node');

        // creating
        $this->on('creating', function(InsertArgs $args) {
            $this->fillColumns();
        });
    }

    protected function treeColumns()
    {
        return (object) [
            'left'      => 'lpos',
            'right'     => 'rpos',
            'parent'    => 'parent'
        ];
    }
}