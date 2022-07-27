
    <body>
        <div class="">
        <form method="get">
        <?php echo $params['vegeredmeny'] ?><br>
    <input class="input-group input-group-sm mb-3" name="mennyit" type="number" value="<?php echo $value ?? 1 ?>"></input><br>
    <select class="form-select form-select-lg mb-3"  name="mirol" value="<?php foreach($params['currencies'] as $currency) { ?>" >
        <option value="<?php echo $currency['label'] ?>" <?php echo $params['sourceCurrency']  === $currency['label'] ? 'selected' : '' ?>>
        <?php echo $currency['name']; ?>
        <?php echo $currency['symbol']; ?>
        </option>
        <?php } ?>
    </select><br>
    
    
    <select class="form-select form-select-lg mb-3"  name="mire" value="<?php foreach($params['currencies'] as $currency) { ?>" >
        <option value="<?php echo $currency['label'] ?>" <?php echo $params['targetCurrency']  === $currency['label'] ? 'selected' : '' ?>>
        <?php echo $currency['name']; ?>
        <?php echo $currency['symbol']; ?>
        </option>
        <?php } ?>
    </select><br>
    <button type="submit" class="btn btn-success"> Küldés</button><br>
    </form>
    
    </div>