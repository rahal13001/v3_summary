<div>
    <style>
       /* General Styling */
       .filament-display-how {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            /* color: #333; */
        }
        
        /* Headings */
        .filament-display-how h1, .filament-display-how h2, .filament-display-how h3,
        .filament-display-how h4, .filament-display-how h5, .filament-display-how h6 {
            margin-top: 1.2em;
            font-weight: bold;
        }
        .filament-display-how h1 { font-size: 2em; }
        .filament-display-how h2 { font-size: 1.75em; }
        .filament-display-how h3 { font-size: 1.5em; }
        .filament-display-how h4 { font-size: 1.25em; }
        .filament-display-how h5 { font-size: 1em; }
        .filament-display-how h6 { font-size: 0.875em; }

        /* Paragraphs */
        .filament-display-how p {
            margin: 0.8em 0;
        }

        /* Links */
        .filament-display-how a {
            color: #007bff;
            text-decoration: none;
        }
        .filament-display-how a:hover {
            text-decoration: underline;
        }

        /* Lists */
        .filament-display-how ul {
            list-style-type: disc; /* bullet points */
            margin: 1em 0 1em 1.5em;
        }

        .filament-display-how ol {
            list-style-type: decimal; /* numbers */
            margin: 1em 0 1em 1.5em;
        }

        .filament-display-how ul li, .filament-display-how ol li {
            margin: 0.5em 0;
        }

        /* Block Quotes */
        .filament-display-how blockquote {
            margin: 1em 0;
            padding: 0.5em 1em;
            border-left: 5px solid #ccc;
            color: #555;
            background: #f9f9f9;
        }

        /* Tables */
        .filament-display-how table {
            width: 100%;
            border-collapse: collapse;
            margin: 1em 0;
        }
        .filament-display-how th, .filament-display-how td {
            border: 1px solid #ddd;
            padding: 0.5em;
        }
        .filament-display-how th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .filament-display-how td {
            background: #fff;
        }

        /* Code Samples */
        .filament-display-how pre {
            background: #f4f4f4;
            border: 1px solid #ddd;
            padding: 1em;
            overflow: auto;
        }
        .filament-display-how code {
            background: #f4f4f4;
            padding: 0.2em 0.4em;
            border-radius: 3px;
        }

        /* Horizontal Rule */
        .filament-display-how hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 1.5em 0;
        }

        /* Inline Formatting */
        .filament-display-how b, .filament-display-how strong {
            font-weight: bold;
        }
        .filament-display-how i, .filament-display-how em {
            font-style: italic;
        }
        .filament-display-how u {
            text-decoration: underline;
        }

        /* Text Alignment */
        .filament-display-how .text-left {
            text-align: left;
        }
        .filament-display-how .text-right {
            text-align: right;
        }
        .filament-display-how .text-center {
            text-align: center;
        }
        .filament-display-how .text-justify {
            text-align: justify;
        }

        /* Colors */
        .filament-display-how .text-color {
            display: inline;
        }
        .filament-display-how .background-color {
            display: inline;
        }

        /* Emoticons (Optional, depending on usage) */
        .filament-display-how .emoticon {
            font-size: 1.2em;
        }
    </style>

    <div class="filament-display-how">
        {!! $state !!}
    </div>

</div>
