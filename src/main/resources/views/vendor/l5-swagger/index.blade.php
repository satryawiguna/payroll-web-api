<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{config('l5-swagger.documentations.'.$documentation.'.api.title')}}</title>
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}" >
  <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32" />
  <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16" />
  <style>
    html {
      box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll;
      font-size: 13px;
    }
    *, *:before, *:after {
      box-sizing: inherit;
    }
    body {
      font-family: 'Roboto', sans-serif !important;
      margin:0; background: #fafafa; font-size: 13px;
    }
    .swagger-ui .topbar {
      display: none;
    }
    .swagger-ui .topbar .download-url-wrapper input[type=text] {
      border: none; background: #ffffffee;
      transition: .2s background-color ease-out;
    }
    .swagger-ui .topbar .download-url-wrapper input[type=text]:hover,
    .swagger-ui .topbar .download-url-wrapper input[type=text]:focus {
      background: #ffffff;
    }
    .swagger-ui .code, .swagger-ui code,
    .swagger-ui .code *, .swagger-ui code *,
    .swagger-ui .opblock-body pre.microlight {
      font-family: 'Fira Code', monospace !important;
      font-weight: 400;
      font-size: .90rem;
    }

    .swagger-ui .info { display: grid; grid-template-columns: 1fr auto auto; gap: 0 8px; margin: 24px 0 16px; }
    .swagger-ui .info .main { grid-column: 1 / span 3; }
    .swagger-ui .info .description p { margin: 0; }
    .swagger-ui .info li, .swagger-ui .info p, .swagger-ui .info table,
    .swagger-ui .info a {
      font-size: 1rem;
    }
    .swagger-ui .info .title {
      font-weight: 400;
      font-size: 2.4rem;
    }
    .swagger-ui .info a {
      color: #0288d1;
    }
    .swagger-ui .info hgroup.main {
      margin-bottom: 8px;
    }
    .swagger-ui .info hgroup.main a {
      font-size: .95rem;
    }
    .swagger-ui .info .title small pre {
      font-size: .65rem; padding: 0 2px;
    }
    .swagger-ui button, .swagger-ui input, .swagger-ui optgroup, .swagger-ui select, .swagger-ui textarea {
      font-size: 1rem;
    }
    .swagger-ui .topbar .download-url-wrapper .download-url-button {
      font-weight: normal;
      font-size: 1rem;
    }
    .swagger-ui .scheme-container .schemes > div {
      display: flex; align-items: center;
    }
    .swagger-ui .scheme-container .schemes .servers-title {
      margin-right: 8px;
    }
    .swagger-ui .opblock-tag {
      font-size: 1.2rem;
      font-weight: 500;
    }
    .swagger-ui .opblock-tag small {
      font-size: .95rem;
      color: #00000088;
    }
    .swagger-ui .opblock-tag {
      align-items: baseline;
      border-bottom-color: #eee;
    }
    .swagger-ui .expand-methods svg, .swagger-ui .expand-operation svg {
      width: 10px; height: 10px;
    }
    .swagger-ui .opblock .opblock-summary-method {
      font-size: .85rem;
    }
    .swagger-ui .opblock .opblock-summary-operation-id, .swagger-ui .opblock .opblock-summary-path, .swagger-ui .opblock .opblock-summary-path__deprecated {
      font-size: 1rem;
    }
    .swagger-ui .opblock .opblock-summary-description {
      color: #00000088;
    }
    .swagger-ui .opblock .opblock-section-header h4 {
      font-size: 1rem;
    }
    table.parameters thead {
      display: none;
    }
    table.parameters,  table.parameters tr, table.parameters th, table.parameters td  {
      display: block;
    }
    table.parameters tbody {
      display: grid; grid-template-columns: repeat(2, 1fr); gap: 0 8px;
    }
    .swagger-ui .btn-group {
      padding-top: 0;
    }
    .swagger-ui .execute-wrapper {
      padding-top: 0;
    }

    table.parameters .parameters-col_name {
      display: flex; align-items: center;
    }
    table.parameters .parameters-col_name .parameter__name,
    table.parameters .parameters-col_name .parameter__type,
    table.parameters .parameters-col_name .parameter__in {
      margin: 0 0 0 4px;
      font-size: 1rem;
    }
    table.parameters .parameters-col_name .parameter__name {
      font-weight: 500;
    }
    table.parameters .parameters-col_name .parameter__type,
    table.parameters .parameters-col_name .parameter__in {
      font-size: .85rem;
      font-weight: 400;
    }
    table.parameters .parameters-col_name .parameter__type:before {
      content: '—'; display: inline-block; margin-right: 4px;
    }
    table.parameters .parameter__name.required:after {
      top: 0;
    }
    .swagger-ui .parameters-col_description {
      margin-bottom: 1rem; width: initial;
    }
    table.parameters .parameters-col_description p {
      display: none;
    }
    .swagger-ui table tbody tr td:first-of-type {
      padding: 0;
    }
    .swagger-ui table tbody tr td {
      padding: 0;
    }
    .swagger-ui input[type=email], .swagger-ui input[type=file], .swagger-ui input[type=password], .swagger-ui input[type=search], .swagger-ui input[type=text], .swagger-ui textarea {
      margin: 0;
    }
    .swagger-ui .btn {
      font-size: 1rem;
    }
    .swagger-ui .parameters-col_description input[type=text] {
      max-width: none;
    }

    .responses-table thead {
      display: none;
    }
    table.responses-table {
      margin-top: 8px;
    }
    table.responses-table .renderedMarkdown p {
      margin: 0;
    }
    table.responses-table .response-col_links {
      font-size: .95rem;
      color: #00000066;
    }
    table.responses-table tbody tr td {
      padding-bottom: 8px;
    }
    .swagger-ui .download-contents {
      font-size: .95rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: normal;
    }
    svg.arrow {
      width: 10px; height: 10px;
    }
    button svg {
      width: 16px; height: 16px;
    }
  </style>
</head>

<body>

<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0">
  <defs>
    <symbol viewBox="0 0 20 20" id="unlocked">
          <path d="M15.8 8H14V5.6C14 2.703 12.665 1 10 1 7.334 1 6 2.703 6 5.6V6h2v-.801C8 3.754 8.797 3 10 3c1.203 0 2 .754 2 2.199V8H4c-.553 0-1 .646-1 1.199V17c0 .549.428 1.139.951 1.307l1.197.387C5.672 18.861 6.55 19 7.1 19h5.8c.549 0 1.428-.139 1.951-.307l1.196-.387c.524-.167.953-.757.953-1.306V9.199C17 8.646 16.352 8 15.8 8z"></path>
    </symbol>

    <symbol viewBox="0 0 20 20" id="locked">
      <path d="M15.8 8H14V5.6C14 2.703 12.665 1 10 1 7.334 1 6 2.703 6 5.6V8H4c-.553 0-1 .646-1 1.199V17c0 .549.428 1.139.951 1.307l1.197.387C5.672 18.861 6.55 19 7.1 19h5.8c.549 0 1.428-.139 1.951-.307l1.196-.387c.524-.167.953-.757.953-1.306V9.199C17 8.646 16.352 8 15.8 8zM12 8H8V5.199C8 3.754 8.797 3 10 3c1.203 0 2 .754 2 2.199V8z"/>
    </symbol>

    <symbol viewBox="0 0 20 20" id="close">
      <path d="M14.348 14.849c-.469.469-1.229.469-1.697 0L10 11.819l-2.651 3.029c-.469.469-1.229.469-1.697 0-.469-.469-.469-1.229 0-1.697l2.758-3.15-2.759-3.152c-.469-.469-.469-1.228 0-1.697.469-.469 1.228-.469 1.697 0L10 8.183l2.651-3.031c.469-.469 1.228-.469 1.697 0 .469.469.469 1.229 0 1.697l-2.758 3.152 2.758 3.15c.469.469.469 1.229 0 1.698z"/>
    </symbol>

    <symbol viewBox="0 0 20 20" id="large-arrow">
      <path d="M13.25 10L6.109 2.58c-.268-.27-.268-.707 0-.979.268-.27.701-.27.969 0l7.83 7.908c.268.271.268.709 0 .979l-7.83 7.908c-.268.271-.701.27-.969 0-.268-.269-.268-.707 0-.979L13.25 10z"/>
    </symbol>

    <symbol viewBox="0 0 20 20" id="large-arrow-down">
      <path d="M17.418 6.109c.272-.268.709-.268.979 0s.271.701 0 .969l-7.908 7.83c-.27.268-.707.268-.979 0l-7.908-7.83c-.27-.268-.27-.701 0-.969.271-.268.709-.268.979 0L10 13.25l7.418-7.141z"/>
    </symbol>


    <symbol viewBox="0 0 24 24" id="jump-to">
      <path d="M19 7v4H5.83l3.58-3.59L8 6l-6 6 6 6 1.41-1.41L5.83 13H21V7z"/>
    </symbol>

    <symbol viewBox="0 0 24 24" id="expand">
      <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/>
    </symbol>

  </defs>
</svg>

<div id="swagger-ui"></div>

<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"> </script>
<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"> </script>
<script>
window.onload = function() {
  // Build a system
  const ui = SwaggerUIBundle({
    dom_id: '#swagger-ui',

    url: "{!! $urlToDocs !!}",
    operationsSorter: {!! isset($operationsSorter) ? '"' . $operationsSorter . '"' : 'null' !!},
    configUrl: {!! isset($configUrl) ? '"' . $configUrl . '"' : 'null' !!},
    validatorUrl: {!! isset($validatorUrl) ? '"' . $validatorUrl . '"' : 'null' !!},
    oauth2RedirectUrl: "{{ route('l5-swagger.'.$documentation.'.oauth2_callback') }}",

    requestInterceptor: function(request) {
      request.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
      return request;
    },

    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],

    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],

    layout: "StandaloneLayout",

    persistAuthorization: {!! config('l5-swagger.defaults.persist_authorization') ? 'true' : 'false' !!},
  })

  window.ui = ui
}
</script>
</body>

</html>
