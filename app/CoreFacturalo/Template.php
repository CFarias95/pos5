<?php

namespace App\CoreFacturalo;
use Illuminate\Support\Facades\Log;
class Template
{
    public function pdf($base_template, $template, $company, $document, $format_pdf, $account_entry = null)
    {
        if($template === 'credit' || $template === 'debit') {
            $template = 'note';
        }

        $path_template =  $this->validate_template($base_template, $template, $format_pdf);
        // Log::info($document);
        return self::render($path_template, $company, $document, $account_entry);
    }

    public function pdf1($base_template, $template, $company, $document, $establishment, $format_pdf, $account_entry, $user_log)
    {
        $path_template =  $this->validate_template($base_template, $template, $format_pdf);
        // Log::info($document);
        return self::render1($path_template, $company, $document, $establishment,$account_entry, $user_log);
    }

    public function pdf2($base_template, $template, $company, $document, $format_pdf, $id, $index, $account_entry)
    {
        if($template === 'credit' || $template === 'debit') {
            $template = 'note';
        }

        $path_template =  $this->validate_template($base_template, $template, $format_pdf);
        // Log::info($document);
        return self::render3($path_template, $company, $document, $id, $index, $account_entry);
    }

    public function preprintedpdf($base_template, $template, $company, $format_pdf)
    {
        if($template === 'credit' || $template === 'debit') {
            $template = 'note';
        }

        $path_template =  $this->validate_preprinted_template($base_template, $template, $format_pdf);

        return self::preprintedrender($path_template, $company);
    }

    public function xml($template, $company, $document, $clave_acceso)
    {
        return self::render2('xml.'.$template, $company, $document, $clave_acceso);
    }
    //JOINSOFTWARE//
    private function render2($view, $company, $document, $clave_acceso)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view($view, compact('company', 'document','clave_acceso'))->render();
    }

    private function render($view, $company, $document, $account_entry=null)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view($view, compact('company', 'document', 'account_entry'))->render();
    }

    private function render1($view, $company, $document, $establishment, $account_entry, $user_log)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view($view, compact('company', 'document','establishment', 'account_entry','user_log'))->render();
    }

    private function render3($view, $company, $document, $id, $index, $account_entry)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view($view, compact('company', 'document', 'id', 'index', 'account_entry'))->render();
    }

    private function preprintedrender($view, $company)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view($view, compact('company'))->render();
    }

    public function pdfFooter($base_template, $document = null)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer', compact('document'))->render();
    }

    public function pdfHeader($base_template, $company, $document = null)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.header', compact('company', 'document'))->render();
    }

    public function validate_template($base_template, $template, $format_pdf)
    {
        $path_app_template = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates');
        $path_template_default = 'pdf'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$template.'_'.$format_pdf;
        $path_template = 'pdf'.DIRECTORY_SEPARATOR.$base_template.DIRECTORY_SEPARATOR.$template.'_'.$format_pdf;



        if(file_exists($path_app_template.DIRECTORY_SEPARATOR.$path_template.'.blade.php')) {
            return str_replace(DIRECTORY_SEPARATOR, '.', $path_template);
        }

        return str_replace(DIRECTORY_SEPARATOR, '.', $path_template_default);
    }

    public function validate_preprinted_template($base_template, $template, $format_pdf)
    {
        $path_app_template = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates');
        $path_template_default = 'preprinted_pdf'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$template.'_'.$format_pdf;
        $path_template = 'preprinted_pdf'.DIRECTORY_SEPARATOR.$base_template.DIRECTORY_SEPARATOR.$template.'_'.$format_pdf;



        if(file_exists($path_app_template.DIRECTORY_SEPARATOR.$path_template.'.blade.php')) {
            return str_replace(DIRECTORY_SEPARATOR, '.', $path_template);
        }

        return str_replace(DIRECTORY_SEPARATOR, '.', $path_template_default);
    }


    public function pdfFooterTermCondition($base_template, $document)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer_term_condition', compact('document'))->render();
    }


    public function pdfFooterLegend($base_template, $document)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer_legend', compact('document'))->render();
    }

    public function pdfFooterBlank($base_template, $document)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer_blank', compact('document'))->render();
    }

    public function pdfFooterDispatch($base_template, $document)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer_dispatch', compact('document'))->render();
    }


    /**
     *
     * Renderizar pdf por nombre sin considerar formato
     *
     * @param  string $base_template
     * @param  string $template
     * @param  mixed $company
     * @param  mixed $document
     * @return mixed
     */
    public function pdfWithoutFormat($base_template, $template, $company, $document)
    {
        $path_template =  $this->validateTemplateWithoutFormat($base_template, $template);
        return self::render($path_template, $company, $document);
    }


    /**
     *
     * Validar si existe el template
     *
     * @param  string $base_template
     * @param  string $template
     * @return string
     */
    public function validateTemplateWithoutFormat($base_template, $template)
    {
        $path_app_template = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates');
        $path_template_default = 'pdf'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$template;
        $path_template = 'pdf'.DIRECTORY_SEPARATOR.$base_template.DIRECTORY_SEPARATOR.$template;

        if(file_exists($path_app_template.DIRECTORY_SEPARATOR.$path_template.'.blade.php')) return str_replace(DIRECTORY_SEPARATOR, '.', $path_template);

        return str_replace(DIRECTORY_SEPARATOR, '.', $path_template_default);
    }

}
