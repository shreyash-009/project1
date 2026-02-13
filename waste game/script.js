// ---------- Data ----------
const wasteData = {
  "Banana Peel":"Biodegradable","Plastic Bottle":"Non-biodegradable","Broken Mirror":"Brittle",
  "Apple Core":"Biodegradable","Polythene Bag":"Non-biodegradable","Glass Bottle":"Brittle",
  "Vegetable Scraps":"Biodegradable","Aluminum Can":"Non-biodegradable","Ceramic Plate":"Brittle",
  "Tea Leaves":"Biodegradable","Candy Wrapper":"Non-biodegradable","Porcelain Cup":"Brittle",
  "Rice":"Biodegradable","Plastic Straw":"Non-biodegradable","Light Bulb":"Brittle",
  "Used Tissue":"Biodegradable","Styrofoam Cup":"Non-biodegradable","Glass Vase":"Brittle",
  "Egg Shells":"Biodegradable","Chip Packet":"Non-biodegradable","Broken Tile":"Brittle",
  "Coffee Grounds":"Biodegradable","Plastic Spoon":"Non-biodegradable","Window Glass":"Brittle",
  "Leaves":"Biodegradable","Tin Can":"Non-biodegradable","Ceramic Mug":"Brittle",
  "Old Newspaper":"Biodegradable","Shampoo Bottle":"Non-biodegradable","Porcelain Figurine":"Brittle",
  "Cardboard Box":"Biodegradable","Battery":"Non-biodegradable","Glass Fragment":"Brittle",
  "Potato Peel":"Biodegradable","Plastic Fork":"Non-biodegradable","Flower Pot (Ceramic)":"Brittle",
  "Onion Peel":"Biodegradable","Old Pen":"Non-biodegradable","Mirror Piece":"Brittle",
  "Leftover Food":"Biodegradable","Plastic Container":"Non-biodegradable","Wine Glass":"Brittle",
  "Cotton Cloth":"Biodegradable","PVC Pipe":"Non-biodegradable","Glass Shard":"Brittle",
  "Used Cloth":"Biodegradable","Polythene Wrapper":"Non-biodegradable","Egg Carton":"Biodegradable",
  "Milk Packet (Plastic)":"Non-biodegradable","Coconut Husk":"Biodegradable","Plastic Lid":"Non-biodegradable",
  "Instant Noodles Packet":"Non-biodegradable","Potato Skin":"Biodegradable","Paper Bag":"Biodegradable",
  "Candy":"Biodegradable","Milk Carton (Tetra Pak)":"Non-biodegradable","Bread":"Biodegradable",
  "Styrofoam Plate":"Non-biodegradable","Coffee Filter":"Biodegradable","Tea Bag":"Biodegradable",
  "Shoe":"Non-biodegradable","Used Notebook Paper":"Biodegradable","Plastic Knife":"Non-biodegradable",
  "Used Tissue Paper":"Biodegradable","Aluminum Foil":"Non-biodegradable","Paper Plate":"Biodegradable",
  "Rice Bag (Plastic)":"Non-biodegradable","Vegetable Stems":"Biodegradable","Fruit Peels":"Biodegradable",
  "Coconut Shell":"Biodegradable","Plastic Wrapper":"Non-biodegradable","Candy Wrapper":"Non-biodegradable",
  "Old Socks":"Biodegradable","Cardboard Tube":"Biodegradable","Packaging Paper":"Biodegradable",
  "Wrapping Paper":"Biodegradable","Newspaper Sheets":"Biodegradable","Plastic Cup":"Non-biodegradable"
};

// Suggestions
const suggestions = {};
for(let item in wasteData){
  const cat = wasteData[item];
  if(cat==="Biodegradable") suggestions[item] = `Organic waste decomposes naturally, so classify "${item}" as Biodegradable.`;
  else if(cat==="Non-biodegradable") suggestions[item] = `"${item}" does not decompose easily; classify it as Non-biodegradable.`;
  else suggestions[item] = `"${item}" is fragile and belongs to the Brittle category.`;
}

// ---------- Variables ----------
let allItems = Object.keys(wasteData);
let gameItems = [];
let currentIndex = 0;
let score = 0;
let correctCount = 0;
const totalRounds = 10;

// DOM Elements
const itemNameEl = document.getElementById("item-name");
const feedbackEl = document.getElementById("feedback");
const scoreEl = document.getElementById("score");
const dustbins = document.querySelectorAll(".dustbin");
const playAgainBtn = document.getElementById("play-again");
const finalScoreEl = document.getElementById("final-score");
const correctCountEl = document.getElementById("correct-count");
const gameOverEl = document.getElementById("game-over");
const gameArea = document.getElementById("game-area");

// ---------- Functions ----------
function shuffle(array){
  for(let i=array.length-1;i>0;i--){
    const j=Math.floor(Math.random()*(i+1));
    [array[i],array[j]]=[array[j],array[i]];
  }
  return array;
}

function initGameItems(){
  gameItems = shuffle(allItems).slice(0,totalRounds);
  currentIndex = 0;
  score = 0;
  correctCount = 0;
  scoreEl.textContent = score;
  feedbackEl.textContent = "";
  gameOverEl.style.display = "none";
  gameArea.style.display = "block";
  showItem();
}

function showItem(){
  if(currentIndex<totalRounds){
    const currentItem = gameItems[currentIndex];
    itemNameEl.textContent = currentItem;
    itemNameEl.setAttribute("draggable","true");
  } else {
    endGame();
  }
}

// ---------- Drag & Drop ----------
itemNameEl.addEventListener("dragstart", e=>{
  e.dataTransfer.setData("text/plain", itemNameEl.textContent);
});

// Dustbins
dustbins.forEach(bin=>{
  bin.addEventListener("dragover", e=> e.preventDefault());
  bin.addEventListener("drop", e=>{
    const draggedItem = e.dataTransfer.getData("text");
    const correctCategory = wasteData[draggedItem];

    if(correctCategory === bin.dataset.category){
      score += 10;
      correctCount++;
      feedbackEl.textContent = `✅ Correct! +10 points`;
      feedbackEl.style.color="#00ff00";
      bin.classList.add("correct-drop");
      setTimeout(()=>bin.classList.remove("correct-drop"), 500);
    } else {
      feedbackEl.textContent = `❌ Wrong! Correct: ${correctCategory}\n💡 ${suggestions[draggedItem]}`;
      feedbackEl.style.color="#ff6347";
    }

    scoreEl.textContent = score;
    currentIndex++;
    showItem();
  });
});

// Background improper drop
gameArea.addEventListener("dragover", e => e.preventDefault());
gameArea.addEventListener("drop", e => {
  const dropTarget = e.target;
  const draggedItem = e.dataTransfer.getData("text");
  if(!dropTarget.classList.contains("dustbin") && draggedItem){
    feedbackEl.textContent = `⚠️ Improper Waste Management! "${draggedItem}" should go into a dustbin.`;
    feedbackEl.style.color="#ff4500";
    // Optional penalty
    score = Math.max(0, score - 5);
    scoreEl.textContent = score;
    currentIndex++;
    showItem();
  }
});

// ---------- End Game ----------
function endGame(){
  gameArea.style.display="none";
  finalScoreEl.textContent = `Your Score: ${score} / ${totalRounds*10}`;
  correctCountEl.textContent = `You correctly classified ${correctCount} out of ${totalRounds} items.`;
  gameOverEl.style.display = "block";
  gameOverEl.scrollIntoView({behavior:"smooth"});
}

// ---------- Play Again ----------
playAgainBtn.addEventListener("click", initGameItems);

// ---------- Initialize ----------
initGameItems();
