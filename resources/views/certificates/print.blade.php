<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $settings['title'] ?? __('Certificado') }} - {{ $certificate->certificate_code }}</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        html, body { height: 100%; margin: 0; font-family: {{ $settings['font_family'] }}; -webkit-print-color-adjust: exact; }
        .page { width: 100%; height: 100vh; display: flex; align-items: center; justify-content: center; background: #f7fafc; }
        .certificate {
            width: 90%;
            height: 80vh;
            padding: 40px;
            box-sizing: border-box;
            position: relative;
            background-color: white;
            border: {{ $settings['border_width'] }} solid {{ $settings['frame_color'] }};
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* background image (if provided) */
        .certificate::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('{{ $settings['background_image_url'] ?? '' }}');
            background-size: cover;
            background-position: center;
            opacity: {{ $settings['watermark_opacity'] ?? 0.06 }};
            pointer-events: none;
        }

        .title { font-size: 30px; letter-spacing: 1px; text-align: center; color: {{ $settings['frame_color'] }}; }
        .recipient { font-size: 40px; text-align: center; font-weight: 700; margin-top: 8px; }
        .meta { text-align: center; margin-top: 8px; font-size: 16px; color: #4b5563; }
        .footer { display:flex; justify-content:space-between; align-items:center; }
        .signature { text-align: center; }
        .signature img { max-width: {{ $settings['signature_max_width'] }}; height: auto; display:block; margin: 0 auto; }
        .code { font-size: 12px; color: #6b7280; text-align: right; }
        .print-button { position: absolute; top: 16px; right: 16px; }

        @media print {
            .print-button { display:none; }
            .page { height: auto; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="certificate">
            <button class="print-button" onclick="window.print()" style="padding:8px 12px;border-radius:6px;background:#111827;color:white;border:0;">{{ __('Imprimir') }}</button>

            <div>
                <div class="title">{{ $settings['title'] ?? __('Certificado de Conclusão') }}</div>

                <div class="meta" style="margin-top:20px;">
                    {{ __('Certificamos que') }}
                </div>

                <div class="recipient">
                    {{ $certificate->student->full_name ?? __('Nome do aluno') }}
                </div>

                <div class="meta" style="margin-top:14px; max-width:70%; margin-left:auto; margin-right:auto; line-height:1.6;">
                    {{ __('concluiu com êxito e cumpriu todos os requisitos acadêmicos e práticos do curso intitulado') }}
                    <strong>“{{ $certificate->course->title ?? __('Curso') }}”</strong>,
                    {{ __('demonstrando compromisso, participação e desempenho satisfatório ao longo do programa.') }}
                </div>

                <div class="meta" style="margin-top:10px;">
                    {{ __('Duração do curso:') }} {{ $certificate->start_date?->format('M d, Y') ?? '' }}
                    — {{ $certificate->end_date?->format('M d, Y') ?? '' }}
                </div>

                @if($certificate->course?->workload)
                    <div class="meta">
                        {{ __('Carga horária total:') }} {{ $certificate->course->workload }} {{ __('horas') }}
                    </div>
                @endif

                <div class="meta" style="margin-top:14px; max-width:75%; margin-left:auto; margin-right:auto; font-size:14px;">
                    {{ __('Este certificado é emitido para fins de registro e verificação e pode ser validado pelo') }}
                    {{ __('código único informado abaixo.') }}
                </div>
            </div>


            <div class="footer">
                <div>
                    <div class="signature">
                        @if($certificate->instructor && $certificate->instructor->signature_image)
                            <img src="{{ $certificate->instructor->signature_image }}" alt="{{ $certificate->instructor->full_name }} {{ __('assinatura') }}">
                        @else
                            <div style="height:40px;"></div>
                        @endif
                        <div style="margin-top:8px;font-weight:600;">{{ $certificate->instructor->full_name ?? '' }}</div>
                        <div style="font-size:12px;color:#6b7280;">{{ __('Instrutor') }}</div>
                    </div>
                </div>

                <div class="code">
                    {{ __('Código:') }} {{ $certificate->certificate_code }}<br>
                    {{ __('Emitido:') }} {{ $certificate->issue_date?->format('M d, Y') ?? now()->format('M d, Y') }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>