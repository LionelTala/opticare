<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossier patient - {{ $patient->nom }} {{ $patient->prenom }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 { font-size: 22px; color: #2d3748; margin-bottom: 5px; }
        h2 { font-size: 15px; color: #2d3748; border-bottom: 2px solid #2d3748; padding-bottom: 5px; margin-top: 25px; }
        h3 { font-size: 12px; color: #4a5568; margin-top: 15px; margin-bottom: 5px; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #2d3748;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .cabinet-info { text-align: right; font-size: 10px; }
        .info-block {
            background: #f7fafc;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .info-block p { margin: 4px 0; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        th {
            background: #2d3748;
            color: white;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        td {
            padding: 5px 6px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:nth-child(even) { background: #f7fafc; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-paye { background: #c6f6d5; color: #22543d; }
        .badge-impaye { background: #fed7d7; color: #742a2a; }
        .badge-partiel { background: #feebc8; color: #7b341e; }
        .badge-termine { background: #c6f6d5; color: #22543d; }
        .badge-en-cours { background: #bee3f8; color: #2a4365; }
        .badge-annule { background: #e2e8f0; color: #4a5568; }
        .prescription-table { margin-top: 5px; }
        .prescription-table th { background: #4a5568; font-size: 9px; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
        .page-break { page-break-after: always; }
        .empty { color: #a0aec0; font-style: italic; font-size: 10px; }
    </style>
</head>
<body>

    {{-- EN-TÊTE --}}
    <div class="header">
        <div>
            <h1>Dossier Patient</h1>
            <p style="color: #718096; margin: 0;">Généré le {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
        <div class="cabinet-info">
            <strong>{{ $cabinet->nom }}</strong><br>
            {{ $cabinet->adresse }}<br>
            {{ $cabinet->ville }}<br>
            Tél : {{ $cabinet->telephone }}
        </div>
    </div>

    {{-- IDENTITÉ --}}
    <h2>Identité du patient</h2>
    <div class="info-block">
        <p><strong>Nom :</strong> {{ $patient->nom }} {{ $patient->prenom }}</p>
        <p><strong>Téléphone :</strong> {{ $patient->telephone }}</p>
        @if($patient->email)<p><strong>Email :</strong> {{ $patient->email }}</p>@endif
        @if($patient->date_naissance)
            <p><strong>Date de naissance :</strong> {{ $patient->date_naissance->format('d/m/Y') }} ({{ $patient->date_naissance->age }} ans)</p>
        @endif
        @if($patient->ville)<p><strong>Ville :</strong> {{ $patient->ville }}</p>@endif
        @if($patient->adresse)<p><strong>Adresse :</strong> {{ $patient->adresse }}</p>@endif
    </div>

    {{-- CONSULTATIONS --}}
    <h2>Historique des consultations ({{ $consultations->count() }})</h2>
    @if($consultations->isEmpty())
        <p class="empty">Aucune consultation enregistrée.</p>
    @else
        @foreach($consultations as $consultation)
            <h3>Consultation du {{ \Carbon\Carbon::parse($consultation->date_consultation)->format('d/m/Y à H:i') }}</h3>
            <p><strong>Motif :</strong> {{ $consultation->motif }}</p>
            <p><strong>Opticien :</strong> {{ $consultation->opticien->prenom ?? '' }} {{ $consultation->opticien->nom ?? '' }}</p>
            <p><strong>Statut :</strong>
                <span class="badge badge-{{ $consultation->statut === 'terminee' ? 'termine' : 'en-cours' }}">
                    {{ $consultation->statut }}
                </span>
            </p>

            @if($consultation->od_sphere || $consultation->og_sphere)
                <table class="prescription-table">
                    <thead>
                        <tr>
                            <th>Œil</th>
                            <th>Sphère</th>
                            <th>Cylindre</th>
                            <th>Axe</th>
                            <th>Addition</th>
                            <th>Acuité loin</th>
                            <th>Acuité près</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>OD (Droit)</strong></td>
                            <td>{{ $consultation->od_sphere ?? '-' }}</td>
                            <td>{{ $consultation->od_cylindre ?? '-' }}</td>
                            <td>{{ $consultation->od_axe ?? '-' }}</td>
                            <td>{{ $consultation->od_addition ?? '-' }}</td>
                            <td>{{ $consultation->od_acuite_loin ?? '-' }}</td>
                            <td>{{ $consultation->od_acuite_pres ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>OG (Gauche)</strong></td>
                            <td>{{ $consultation->og_sphere ?? '-' }}</td>
                            <td>{{ $consultation->og_cylindre ?? '-' }}</td>
                            <td>{{ $consultation->og_axe ?? '-' }}</td>
                            <td>{{ $consultation->og_addition ?? '-' }}</td>
                            <td>{{ $consultation->og_acuite_loin ?? '-' }}</td>
                            <td>{{ $consultation->og_acuite_pres ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            @if($consultation->observations)
                <p><strong>Observations :</strong> {{ $consultation->observations }}</p>
            @endif
        @endforeach
    @endif

    <div class="page-break"></div>

    {{-- ORDONNANCES --}}
    <h2>Ordonnances ({{ $ordonnances->count() }})</h2>
    @if($ordonnances->isEmpty())
        <p class="empty">Aucune ordonnance enregistrée.</p>
    @else
        @foreach($ordonnances as $ordonnance)
            <h3>Ordonnance n° {{ $ordonnance->numero }} - émise le {{ $ordonnance->date_emission->format('d/m/Y') }}</h3>
            <table class="prescription-table">
                <thead>
                    <tr>
                        <th>Œil</th>
                        <th>Sphère</th>
                        <th>Cylindre</th>
                        <th>Axe</th>
                        <th>Addition</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>OD</strong></td>
                        <td>{{ $ordonnance->od_sphere ?? '-' }}</td>
                        <td>{{ $ordonnance->od_cylindre ?? '-' }}</td>
                        <td>{{ $ordonnance->od_axe ?? '-' }}</td>
                        <td>{{ $ordonnance->od_addition ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>OG</strong></td>
                        <td>{{ $ordonnance->og_sphere ?? '-' }}</td>
                        <td>{{ $ordonnance->og_cylindre ?? '-' }}</td>
                        <td>{{ $ordonnance->og_axe ?? '-' }}</td>
                        <td>{{ $ordonnance->og_addition ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
            @if($ordonnance->ecart_pupillaire)
                <p><strong>Écart pupillaire :</strong> {{ $ordonnance->ecart_pupillaire }}</p>
            @endif
            @if($ordonnance->recommandations)
                <p><strong>Recommandations :</strong> {{ $ordonnance->recommandations }}</p>
            @endif
            <p><strong>Validité :</strong> {{ $ordonnance->date_validite ? $ordonnance->date_validite->format('d/m/Y') : 'Non spécifiée' }}</p>
        @endforeach
    @endif

    {{-- COMMANDES --}}
    <h2>Commandes d'équipement ({{ $commandes->count() }})</h2>
    @if($commandes->isEmpty())
        <p class="empty">Aucune commande enregistrée.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>N° Monture</th>
                    <th>Type verre</th>
                    <th>Teinte</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commandes as $commande)
                    <tr>
                        <td>{{ $commande->numero_monture ?? '-' }}</td>
                        <td>{{ $commande->type_verre ?? '-' }}</td>
                        <td>{{ $commande->teinte ?? '-' }}</td>
                        <td>
                            <span class="badge badge-{{ $commande->statut === 'termine' ? 'termine' : 'en-cours' }}">
                                {{ $commande->statut }}
                            </span>
                        </td>
                        <td>{{ $commande->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="page-break"></div>

    {{-- FACTURES --}}
    <h2>Factures ({{ $factures->count() }})</h2>
    @if($factures->isEmpty())
        <p class="empty">Aucune facture enregistrée.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Payé</th>
                    <th>Solde</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factures as $facture)
                    <tr>
                        <td>{{ $facture->numero }}</td>
                        <td>{{ $facture->type }}</td>
                        <td>{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($facture->montant_paye, 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($facture->montant - $facture->montant_paye, 0, ',', ' ') }} FCFA</td>
                        <td>
                            <span class="badge badge-{{ $facture->statut === 'paye' ? 'paye' : ($facture->statut === 'partiel' ? 'partiel' : 'impaye') }}">
                                {{ $facture->statut }}
                            </span>
                        </td>
                        <td>{{ $facture->date_emission->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTAL --}}
        @php
            $totalFacture = $factures->sum('montant');
            $totalPaye = $factures->sum('montant_paye');
        @endphp
        <div class="info-block" style="margin-top: 20px;">
            <p><strong>Total facturé :</strong> {{ number_format($totalFacture, 0, ',', ' ') }} FCFA</p>
            <p><strong>Total payé :</strong> {{ number_format($totalPaye, 0, ',', ' ') }} FCFA</p>
            <p><strong>Solde restant :</strong> {{ number_format($totalFacture - $totalPaye, 0, ',', ' ') }} FCFA</p>
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        Document généré automatiquement par OptiCare - {{ now()->format('d/m/Y à H:i') }} - Confidentiel
    </div>

</body>
</html>