<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 5/22/18
 * Time: 4:39 PM
 */

namespace FK3\Models\Traits;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait DataTrait
{
    /**
     * Assign an array of parsed fields
     * @var array
     */
    public $dtMap = [];

    /**
     * What Model are we working with?
     * @var
     */
    public $dtModel;

    /**
     * If a field should be an anchor we will sprintf with the id
     * @var
     */
    public $dtLinkPrefix;

    /**
     * Array of Relationships to Eager load
     * @var array
     */
    public $dtWith = [];

    /**
     * Joins for sorting by relationships
     * @var array
     */
    public $dtJoins = [];
    /**
     * Main Rendering Method
     * @param Request $request
     * @return array
     */
    public function dtRender(Request $request): array
    {
        // Set Basic Properties given by Datatables
        $column = $request->get('order')[0]['column'] ?: 0;
        $mode = $request->get('order')[0]['dir'];
        $start = $request->get('start');
        $length = $request->get('length');
        $search = $request->get('search')['value'];
        $countedColumns = [];
        foreach ($this->dtMap as $field => $opts)
        {
            $countedColumns[] = empty($opts['sorts']) ? $field : $opts['sorts'];
        }
        $results = new $this->dtModel;
        if (!empty($this->dtJoins))
        {
            foreach ($this->dtJoins as $relation => $query)
            {
                $results = $results->leftJoin($relation, $query[0], $query[1], $query[2]);
            }
        }
        if (!empty($this->dtWith))
        {
            $results = $results->with($this->dtWith);
        }
        if ($search)
        {
            $results = $results->where(function ($t) use ($search) {
                foreach ($this->dtMap as $field => $opts)
                {
                    if (!empty($opts['searchable']) && $opts['searchable'] === true)
                    {
                        $t->orWhere($field, 'like', "%$search%");
                    }
                }
            });
            foreach ($this->dtMap as $field => $opts) // itterate through relation mappings
            {
                if (!empty($opts['searchable']) && $opts['searchable'] !== true && !empty($opts['relation']))
                {
                    $rField = $opts['searchable'];
                    $results = $results->orWhereHas($opts['relation'], function ($t) use ($rField, $search) {
                        $t->where($rField, 'like', "%$search%");
                    });
                }
            }
        } // If searching
         $results = $results->orderBy($countedColumns[$column], $mode);
        $results = $results->skip($start);
        $results = $results->take($length);
        $results = $results->get([(new $this->dtModel)->table . ".*"]); // manually set table for join ID artifacting
       /* if ($mode == 'asc')
        {
            $results = $results->sortBy($countedColumns[$column]);
        }
        else $results = $results->sortByDesc($countedColumns[$column]);
       */
        $rows = [];
        foreach ($results as $result)
        {
            $row = [];
            foreach ($this->dtMap as $field => $opts)
            {
                if (!empty($opts['link']))
                {
                    $field = !empty($opts['output']) ? $opts['output'] : $field;
                    $row[] = sprintf("<a href='$this->dtLinkPrefix'>{$result->$field}</a>", $result->id);
                }
                elseif (!empty($opts['output']))
                {
                    $field = $opts['output'];
                    $row[] = $result->$field;
                }
                else
                {
                    $row[] = $result->$field;
                }
            }
            $rows[] = $row;
        }
        return [
            'draw'            => $request->get('draw', 0),
            'recordsTotal'    => sizeOf($results),
            'recordsFiltered' => (new $this->dtModel)->all()->count(),
            'data'            => $rows
        ];
    }


}
