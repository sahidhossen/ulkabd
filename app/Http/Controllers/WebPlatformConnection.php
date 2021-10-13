<?php

namespace App\Http\Controllers;

use App\Agents;
use Highlight\Highlighter;
use Illuminate\Support\Facades\URL;

class WebPlatformConnection extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $current_agent = Agents::getCurrentAgent();
        $agent_code = $current_agent["attributes"]["agent_code"];

        $highlighter = new Highlighter();
        $language = 'html';
        $code =
'<div id="__usha__chatbot__mounter__"></div>
<script>
    window.USHA_AGENT_KEY = "' . $agent_code . '";
</script>
<script async defer src="' . URL::to('/') . '/usha-chatbot.styles.min.js"></script>
<script async defer src="' . URL::to('/') . '/usha-chatbot.min.js"></script>';

        $markupHighlightedCodeObject = $highlighter->highlight($language, $code);
        return view('web.connect')->with(['code' => $markupHighlightedCodeObject]);
    }
}
