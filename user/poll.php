




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Poll System</title>
    
    <style>
        body {
      font-family: Arial, sans-serif;
      background: #f0f8ff;
      display: flex;
      justify-content: center;
      padding-top: 50px;
    }
    
    .poll-container {
      background: white;
      padding: 20px 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.2);
      width: 300px;
    }
    
    h2 {
      margin-bottom: 20px;
      font-size: 18px;
    }
    
    label {
      display: block;
      margin-bottom: 10px;
    }
    
    button {
      margin-top: 10px;
      padding: 8px 20px;
      background-color: teal;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    
    #result {
      margin-top: 20px;
    }
    
    .result-bar {
      height: 20px;
      background-color: lightgreen;
      margin-bottom: 5px;
      border-radius: 4px;
    }
    </style>
  

</head>
<body>
  <div class="poll-container">
    <h2>Which is your favorite programming language?</h2>
    <form id="pollForm">
      <label><input type="radio" name="language" value="Python"> Python</label>
      <label><input type="radio" name="language" value="JavaScript"> JavaScript</label>
      <label><input type="radio" name="language" value="Java"> Java</label>
      <label><input type="radio" name="language" value="PHP"> PHP</label>
      <button type="submit">Vote</button>
    </form>
    <div id="result"></div>
  </div>
  
  <script>
  document.getElementById("pollForm").addEventListener("submit", function (e) {
    e.preventDefault();
  
    const formData = new FormData(this);
  
    fetch("vote.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        displayResult(data);
      });
  });
  
  function displayResult(results) {
    const resultDiv = document.getElementById("result");
    resultDiv.innerHTML = "<h3>Results:</h3>";
  
    for (const lang in results) {
      const percentage = results[lang].percentage;
      resultDiv.innerHTML += `
        <div>${lang}: ${percentage}%</div>
        <div class="result-bar" style="width:${percentage}%"></div>
      `;
    }
  }
  </script>
</body>
</html>
