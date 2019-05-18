<?php namespace Vocalogic\Html;

class TableBuilder
{

    use GenericBuilder;

    protected $striped = false;

    protected $bordered = false;

    protected $hover = false;

    protected $condensed = false;

    protected $responsive = false;

    protected $datatable = false;

    protected $head = [];

    protected $body = [];

    protected $foot = [];

    protected $headClass = null;

    /**
     * Turn the striped feature on.
     *
     * @return Vocalogic\Html\TableBuilder
     */
    public function striped()
    {
        $this->striped = true;
        return $this;
    }

    /**
     * Turn the bordered feature on.
     *
     * @return Vocalogic\Html\TableBuilder
     */
    public function bordered()
    {
        $this->bordered = true;
        return $this;
    }

    /**
     * Turn the hover feature on.
     *
     * @return Vocalogic\Html\TableBuilder
     */
    public function hover()
    {
        $this->hover = true;
        return $this;
    }

    /**
     * Turn the condensed feature on.
     *
     * @return Vocalogic\Html\TableBuilder
     */
    public function condensed()
    {
        $this->condensed = true;
        return $this;
    }

    /**
     * Turn the responsive feature on.
     *
     * @return Vocalogic\Html\TableBuilder
     */
    public function responsive()
    {
        $this->responsive = true;
        return $this;
    }

    /**
     * Turn the datatable feature on.
     *
     * @return Vocalogic\Html\TableBuilder
     */
    public function datatable()
    {
        $this->datatable = true;
        return $this;
    }

    /**
     * Obtain Bootstrap table classes.
     *
     * @return string
     */
    public function getTableClasses()
    {
        $classes = explode(' ', empty($this->attributes['class']) ? '' : $this->attributes['class']);

        $classes[] = 'table';

        if ($this->striped)
        {
            $classes[] = 'table-striped';
        }

        if ($this->bordered)
        {
            $classes[] = 'table-bordered';
        }

        if ($this->hover)
        {
            $classes[] = 'table-hover';
        }

        if ($this->condensed)
        {
            $classes[] = 'table-condensed';
        }

        if ($this->datatable)
        {
            $classes[] = 'datatable';
        }

        return join(' ', $classes);
    }

    /**
     * Set the table head.
     *
     * @param  mixed   $head
     * @param  boolean $copyAtFoot
     * @return Vocalogic\Html\TableBuilder
     */
    public function head($head, $copyAtFoot = false, $class = null)
    {
        $this->headClass = $class;
        $this->head = $head;
        if ($copyAtFoot)
        {
            $this->foot = $head;
        }
        return $this;
    }

    /**
     * Set the table body.
     *
     * @param  mixed $body
     * @return Vocalogic\Html\TableBuilder
     */
    public function body($body)
    {
        $this->body = $body;
        return $this;
    }


    /**
     * Set the table foot.
     *
     * @param  mixed $foot
     * @return Vocalogic\Html\TableBuilder
     */
    public function foot($foot)
    {
        $this->foot = $foot;
        return $this;
    }

    /**
     * Render the full HTML table.
     *
     * @return string
     */
    public function render()
    {
        $attributes = $this->attributes;

        $attributes['class'] = $this->getTableClasses();

        $attributes = $this->html->attributes($attributes);

        $html = [
            "<table{$attributes}>",
            $this->renderHead(),
            $this->renderBody(),
            $this->renderFoot(),
            "</table>",
        ];

        if ($this->responsive)
        {
            array_unshift($html, '<div class="table-responsive">');
            array_push($html, '</div>');
        }

        return join(PHP_EOL, $html);
    }

    /**
     * Render the table head HTML.
     *
     * @return string
     */
    public function renderHead()
    {
        if (empty($this->head))
        {
            return '';
        }

        if (!is_array($this->head))
        {
            return $this->head;
        }

        $headers = [];

        foreach ($this->head as $key => $header)
        {
            if (is_numeric($key))
            {
                $headers[] = "<th>{$header}</th>";
            }
            else
            {
                $attributes = $this->html->attributes($header);
                $headers[] = "<th{$attributes}>{$key}</th>";
            }
        }

        $html = [
            '<thead class="'.$this->headClass .'">', '<tr>',
            join(PHP_EOL, $headers),
            '</tr>', '</thead>',
        ];


        return join(PHP_EOL, $html);
    }

    /**
     * Render the table body HTML.
     *
     * @return string
     */
    public function renderBody()
    {
        if (empty($this->body))
        {
            return '';
        }

        if (!is_array($this->body))
        {
            return $this->body;
        }

        $rows = [];

        foreach ($this->body as $row)
        {
            $rows[] = $this->renderRow($row);
        }

        $html = [
            '<tbody>',
            join(PHP_EOL, $rows),
            '</tbody>',
        ];

        return join(PHP_EOL, $html);
    }

    /**
     * Render a table row HTML.
     *
     * @return string
     */
    public function renderRow($row)
    {
        if (empty($row))
        {
            return '';
        }

        if (!is_array($row))
        {
            return $row;
        }

        $cells = [];
        $attributes = [];

        foreach ($row as $key => $cell)
        {
            if (substr($key, 0, 1) == '_')
            {
                $attributes[substr($key, 1)] = $cell;
                unset($row[$key]);
                continue;
            }
            $cells[] = $this->renderCell($cell);
        }

        $attributes = $this->html->attributes($attributes);

        $html = [
            "<tr{$attributes}>",
            join(PHP_EOL, $cells),
            '</tr>',
        ];

        return join(PHP_EOL, $html);
    }

    /**
     * Render a table cell HTML.
     *
     * @return string
     */
    public function renderCell($cell)
    {
        if (!is_array($cell))
        {
            return "<td>{$cell}</td>";
        }

        $contents = [];
        $attributes = [];

        foreach ($cell as $key => $value)
        {
            if (substr($key, 0, 1) == '_')
            {
                $attributes[substr($key, 1)] = $value;
                unset($cell[$key]);
                continue;
            }
            $contents[] = $value;
        }

        $attributes = $this->html->attributes($attributes);

        $html = [
            "<td{$attributes}>",
            join(PHP_EOL, $contents),
            '</td>',
        ];

        return join(PHP_EOL, $html);
    }

    /**
     * Render the table foot HTML.
     *
     * @return string
     */
    public function renderFoot()
    {
        if (empty($this->foot))
        {
            return '';
        }

        if (!is_array($this->foot))
        {
            return $this->foot;
        }

        $footers = [];

        foreach ($this->foot as $key => $footer)
        {
            if (is_numeric($key))
            {
                $footers[] = "<th>{$footer}</th>";
            }
            else
            {
                $attributes = $this->html->attributes($footer);
                $footers[] = "<th{$attributes}>{$key}</th>";
            }
        }

        $html = [
            '<tfoot>', '<tr>',
            join(PHP_EOL, $footers),
            '</tr>', '</tfoot>',
        ];

        return join(PHP_EOL, $html);
    }

}
