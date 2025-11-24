<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nutrition Plan PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
        .center { text-align: center; }
        .title { font-size: 18px; font-weight: bold; }
        .section-title { font-size: 13px; font-weight: bold; margin: 8px 0 4px; }
        .meta-line { font-size: 9px; color: #444; margin: 2px 0; }
        .logo { width: 80px; margin: 0 auto 8px; display: block; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px; border-bottom: 1px solid #ddd; text-align: left; }
        thead th { border-bottom: 1px solid #bbb; }
        .badge { display: inline-block; padding: 2px 6px; border: 1px solid #888; border-radius: 4px; font-size: 9px; }
        .big-number { font-size: 16px; font-weight: bold; color: #e67e22; }
        .subtext { font-size: 9px; color: #666; }
        .metric-box { border: 1px solid #ccc; border-radius: 4px; padding: 6px; text-align: center; }
        .grid { display: table; width: 100%; table-layout: fixed; }
        .grid .col { display: table-cell; width: 33.33%; padding: 4px; }
    </style>
</head>
<body>
    <div class="center">
        @if(!empty($logoBase64))
            <img src="{{ $logoBase64 }}" class="logo" alt="Logo">
        @endif
        <div class="title">{{ $plan->plan_name ?? 'Nutrition Plan' }}</div>
    </div>

    <div class="meta-line">Trainer: {{ optional($plan->trainer)->name ?? 'N/A' }} | Client: {{ optional($plan->client)->name ?? 'Unassigned' }}</div>
    <div class="meta-line">Status: {{ ucfirst($plan->status ?? 'inactive') }} | Goal: {{ $plan->goal_type ? ucfirst(str_replace('_',' ',$plan->goal_type)) : 'N/A' }} | Duration: {{ $plan->duration_text }}</div>

    @if(!empty($plan->description))
        <div class="meta-line">{{ $plan->description }}</div>
    @endif

    @if($plan->dailyMacros)
        <div class="section-title">Daily Macros</div>
        <table>
            <thead>
                <tr>
                    <th>Calories</th>
                    <th>Protein (g)</th>
                    <th>Carbs (g)</th>
                    <th>Fats (g)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $plan->dailyMacros->total_calories ?? 0 }}</td>
                    <td>{{ $plan->dailyMacros->protein ?? 0 }}</td>
                    <td>{{ $plan->dailyMacros->carbs ?? 0 }}</td>
                    <td>{{ $plan->dailyMacros->fats ?? 0 }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    @if($plan->recommendations)
        <div class="section-title">Recommendations</div>
        <div class="meta-line">Target Calories: {{ number_format($plan->recommendations->target_calories ?? 0) }}</div>
        @php
            $dist = is_string($plan->recommendations->macro_distribution ?? null)
                ? json_decode($plan->recommendations->macro_distribution, true)
                : ($plan->recommendations->macro_distribution ?? []);
            $goalTypeLabel = $plan->goal_type ? ucfirst(str_replace('_',' ',$plan->goal_type)) : 'N/A';
            $targetCalories = (float) ($plan->recommendations->target_calories ?? 0);
            $bmrVal = (float) ($plan->recommendations->bmr ?? 0);
            $tdeeVal = (float) ($plan->recommendations->tdee ?? 0);
            $calorieAdjustment = $targetCalories - $tdeeVal;
            $calorieAdjustmentSign = $calorieAdjustment > 0 ? '+' : ($calorieAdjustment < 0 ? '-' : '');
            $calorieAdjustmentAbs = abs($calorieAdjustment);
        @endphp
        <div class="meta-line">Current Nutrition Recommendations — Based on calculated BMR and TDEE values</div>
        <div class="center" style="margin:6px 0;">
            <div class="big-number">{{ number_format($targetCalories) }}</div>
            <div class="subtext">Daily Target Calories</div>
            <div class="subtext">Based on {{ $goalTypeLabel }} goal</div>
        </div>
        @if(!empty($dist))
            <div class="grid" style="margin:6px 0;">
                <div class="col">
                    <div class="metric-box">
                        <div style="font-size:12px; color:#28a745; font-weight:bold;">{{ number_format($plan->recommendations->protein ?? 0, 2) }}g</div>
                        <div class="subtext">Protein</div>
                        <div class="subtext">{{ $dist['protein_percentage'] ?? 25 }}%</div>
                    </div>
                </div>
                <div class="col">
                    <div class="metric-box">
                        <div style="font-size:12px; color:#ff9800; font-weight:bold;">{{ number_format($plan->recommendations->carbs ?? 0, 2) }}g</div>
                        <div class="subtext">Carbs</div>
                        <div class="subtext">{{ $dist['carbs_percentage'] ?? 45 }}%</div>
                    </div>
                </div>
                <div class="col">
                    <div class="metric-box">
                        <div style="font-size:12px; color:#dc3545; font-weight:bold;">{{ number_format($plan->recommendations->fats ?? 0, 2) }}g</div>
                        <div class="subtext">Fats</div>
                        <div class="subtext">{{ $dist['fats_percentage'] ?? 30 }}%</div>
                    </div>
                </div>
            </div>
        @endif
        <div class="section-title">Nutrition Calculator</div>
        <table>
            <thead>
                <tr>
                    <th>BMR</th>
                    <th>TDEE</th>
                    <th>Activity Level</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($bmrVal) }}</td>
                    <td>{{ number_format($tdeeVal) }}</td>
                    <td>{{ $plan->recommendations->activity_level ? ucfirst(str_replace('_',' ',$plan->recommendations->activity_level)) : 'N/A' }}</td>
                    <td>{{ $plan->recommendations->calculation_method ? ucfirst(str_replace('_',' ',$plan->recommendations->calculation_method)) : 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
        <div class="grid" style="margin-top:6px;">
            <div class="col">
                <div class="metric-box" style="text-align:left;">
                    <div style="font-weight:bold; margin-bottom:4px;">Metabolic Calculations</div>
                    <div class="meta-line">BMR: {{ number_format($bmrVal) }} cal</div>
                    <div class="meta-line">TDEE: {{ number_format($tdeeVal) }} cal</div>
                    <div class="meta-line">Activity Level: {{ $plan->recommendations->activity_level ? ucfirst(str_replace('_',' ',$plan->recommendations->activity_level)) : 'N/A' }}</div>
                </div>
            </div>
            <div class="col">
                <div class="metric-box" style="text-align:left;">
                    <div style="font-weight:bold; margin-bottom:4px;">Goal Adjustment</div>
                    <div class="meta-line">Calorie Adjustment: {{ $calorieAdjustmentSign }}{{ number_format($calorieAdjustmentAbs) }} cal</div>
                    <div class="meta-line">Goal Type: {{ $goalTypeLabel }}</div>
                    <div class="meta-line">Formula Used: {{ $plan->recommendations->calculation_method ? str_replace('_',' ',$plan->recommendations->calculation_method) : 'N/A' }}</div>
                </div>
            </div>
        </div>
    @endif

    @if($plan->meals && $plan->meals->count())
        <div class="section-title">Meals ({{ $plan->meals->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Servings</th>
                    <th>Calories</th>
                    <th>Protein</th>
                    <th>Carbs</th>
                    <th>Fats</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plan->meals as $meal)
                    <tr>
                        <td>{{ $meal->title }}</td>
                        <td>{{ $meal->meal_type_display }}</td>
                        <td>{{ $meal->servings }}</td>
                        <td>{{ $meal->calories_per_serving ?? 0 }}</td>
                        <td>{{ $meal->protein_per_serving ?? 0 }}</td>
                        <td>{{ $meal->carbs_per_serving ?? 0 }}</td>
                        <td>{{ $meal->fats_per_serving ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($plan->recipes && $plan->recipes->count())
        <div class="section-title">Recipes ({{ $plan->recipes->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plan->recipes as $recipe)
                    <tr>
                        <td>{{ $recipe->title }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($recipe->description, 120) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
