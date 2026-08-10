<div>
    <style>
       /* Report rich text */
       .filament-display-how {
            --how-text: #1f2937;
            --how-muted: #4b5563;
            font-family: inherit;
            color: var(--how-text);
            font-size: 0.95rem;
            line-height: 1.75;
            overflow-wrap: anywhere;
            overflow-x: auto;
        }

        :root.dark .filament-display-how {
            --how-text: #f4f4f5;
            --how-muted: #d4d4d8;
        }
        
        /* Headings */
        .filament-display-how h1, .filament-display-how h2, .filament-display-how h3,
        .filament-display-how h4, .filament-display-how h5, .filament-display-how h6 {
            margin: 1.5em 0 0.55em;
            color: var(--how-text);
            font-weight: bold;
        }
        .filament-display-how h1:first-child, .filament-display-how h2:first-child,
        .filament-display-how h3:first-child, .filament-display-how h4:first-child,
        .filament-display-how h5:first-child, .filament-display-how h6:first-child {
            margin-top: 0;
        }
        .filament-display-how h1 { font-size: 2em; }
        .filament-display-how h2 { font-size: 1.75em; }
        .filament-display-how h3 { font-size: 1.5em; }
        .filament-display-how h4 { font-size: 1.25em; }
        .filament-display-how h5 { font-size: 1em; }
        .filament-display-how h6 { font-size: 0.875em; }

        /* Paragraphs */
        .filament-display-how p {
            margin: 0 0 1rem;
        }

        /* Links */
        .filament-display-how a {
            color: #2563eb;
            text-decoration: underline;
            text-underline-offset: 0.15em;
        }
        .filament-display-how a:hover {
            text-decoration: underline;
        }
        :root.dark .filament-display-how a {
            color: #60a5fa;
        }
        .filament-display-how a:focus-visible {
            border-radius: 0.125rem;
            outline: 2px solid currentColor;
            outline-offset: 2px;
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
            padding: 0.75em 1em;
            border-left: 4px solid #f59e0b;
            color: var(--how-muted);
            background: rgb(245 158 11 / 0.08);
        }

        /* Tables */
        .filament-display-how {
            --how-table-background: #ffffff;
            --how-table-header-background: #f5f5f5;
            --how-table-border: #d1d5db;
            --how-table-text: #111827;
        }

        :root.dark .filament-display-how {
            --how-table-background: transparent;
            --how-table-header-background: transparent;
            --how-table-border: rgba(255, 255, 255, 0.65);
            --how-table-text: #ffffff;
        }

        .filament-display-how table {
            width: 100%;
            min-width: 36rem;
            border-collapse: collapse;
            margin: 1.25rem 0;
            color: var(--how-table-text) !important;
            background-color: var(--how-table-background) !important;
        }
        .filament-display-how th, .filament-display-how td {
            border: 1px solid var(--how-table-border) !important;
            padding: 0.65rem 0.75rem;
            vertical-align: top;
            color: var(--how-table-text) !important;
            background-color: var(--how-table-background) !important;
        }
        .filament-display-how th {
            background-color: var(--how-table-header-background) !important;
            font-weight: bold;
        }

        /* Code Samples */
        .filament-display-how pre {
            background: rgb(243 244 246);
            border: 1px solid rgb(209 213 219);
            border-radius: 0.5rem;
            padding: 1rem;
            overflow: auto;
        }
        .filament-display-how code {
            background: rgb(243 244 246);
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
        }

        :root.dark .filament-display-how pre,
        :root.dark .filament-display-how code {
            background: rgb(39 39 42);
            border-color: rgb(63 63 70);
        }

        /* Horizontal Rule */
        .filament-display-how hr {
            border: none;
            border-top: 1px solid rgb(229 231 235);
            margin: 1.5em 0;
        }

        :root.dark .filament-display-how hr {
            border-color: rgb(63 63 70);
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
        {!! str($state)->sanitizeHtml() !!}
    </div>

</div>
