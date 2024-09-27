// Dimensões da área útil dentro das bordas
const imageWidth = 777; // Largura dentro das bordas
const imageHeight = 737; // Altura dentro das bordas

// Dimensões totais da imagem
const totalWidth = 802;
const totalHeight = 776;

// Cria o palco Konva
const stage = new Konva.Stage({
  container: 'mapGrid',  // ID do div onde o canvas será criado
  width: totalWidth,  // Largura total da imagem
  height: totalHeight  // Altura total da imagem
});

// Cria uma camada
const layer = new Konva.Layer();
stage.add(layer);

// Carrega a imagem e a desenha no canvas
const imageObj = new Image();
imageObj.src = 'data_center.png';  // Certifique-se de usar o caminho correto para sua imagem

imageObj.onload = function() {
  const konvaImage = new Konva.Image({
    x: 0,
    y: 0,
    image: imageObj,
    width: totalWidth,
    height: totalHeight
  });

  // Adiciona a imagem à camada
  layer.add(konvaImage);

  // Chama a função para desenhar os quadrados interativos
  drawRectangles();

  // Renderiza a camada
  layer.draw();
};

// Função para desenhar os quadrados interativos
async function drawRectangles() {
  const rows = 18;
  const cols = 19;
  const rectWidth = imageWidth / cols;
  const rectHeight = imageHeight / rows;
  const offsetX = (totalWidth - imageWidth) / 2;
  const offsetY = (totalHeight - imageHeight) / 2;

  const columns = ["AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "AO", "AP", "AQ", "AR", "AS"];
  const lines = ["00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18"];

  const positionDisplay = document.getElementById('positionDisplay');
  const positionText = document.getElementById('positionText');
  const tomadasCheckbox = document.getElementById('tomadasCheckbox');

  const rects = [];  // Para armazenar os retângulos desenhados

  // Carregar status das tomadas e áreas clicáveis do servidor
  const tomadaStatus = await fetchTomadaStatus();

  for (let i = 0; i < rows; i++) {
    for (let j = 0; j < cols; j++) {
      const cellId = `${columns[j]}${lines[i]}`;

      // Verifica o grupo clicável
      let clickableGroup = '';
      if (tomadaStatus.clickableGroups.availableClickable.includes(cellId)) {
        clickableGroup = 'availableClickable';
      } else if (tomadaStatus.clickableGroups.nonAvailableClickable.includes(cellId)) {
        clickableGroup = 'nonAvailableClickable';
      }

      // Identificar o grupo do quadrado
      let group = '';
      if (tomadaStatus.availableTomadas.includes(cellId)) {
        group = 'availableTomada';
      } else if (tomadaStatus.nonAvailableTomadas.includes(cellId)) {
        group = 'nonAvailableTomada';
      }

      const rect = new Konva.Rect({
        x: (j * rectWidth) + offsetX,
        y: (i * rectHeight) + offsetY,
        width: rectWidth,
        height: rectHeight,
        fill: 'transparent',  // Começa transparente por padrão
        stroke: 'transparent',  // Torna as bordas invisíveis
        strokeWidth: 0,  // Remove a largura da borda
        draggable: false  // Não permite arrastar
      });

      rects.push({ rect, clickableGroup, group, cellId });  // Armazena o retângulo, grupo e o grupo clicável

      // Evento de mouseover para mostrar a posição e mudar a cor nas tomadas ou áreas clicáveis
      rect.on('mouseover', function() {
        positionText.textContent = cellId;  // Exibe a posição no elemento HTML
        positionDisplay.style.display = 'block';  // Torna o display visível

        if (!tomadasCheckbox.checked) {  // Só muda a cor se a checkbox não estiver marcada
          // Define a cor temporariamente ao passar o mouse
          if (group === 'availableTomada') {
            this.fill('rgba(0, 255, 0, 0.8)');  // Verde para tomadas disponíveis
          } else if (group === 'nonAvailableTomada') {
            this.fill('rgba(255, 0, 0, 0.8)');  // Vermelho para tomadas não disponíveis
          } else if (clickableGroup === 'nonAvailableClickable') {
            this.fill('rgba(0, 0, 0, 0.8)');  // Preto para não clicáveis ao passar o mouse
          } else if (clickableGroup === 'availableClickable') {
            this.fill('rgba(0, 0, 0, 0.2)');  // Cinza para clicáveis
          }
          layer.draw();
        }
      });

      // Evento de mouseout para restaurar a cor original
      rect.on('mouseout', function() {
        if (!tomadasCheckbox.checked) {  // Só restaura a cor se a checkbox não estiver marcada
          this.fill('transparent');  // Restaura a cor original
          positionDisplay.style.display = 'none';
          layer.draw();
        }
      });

      // Evento de clique para abrir a página correspondente
      rect.on('click', function() {
        const fileName = `${cellId}.html`;  // Nome da página correspondente ao quadrado
        window.location.href = `grid-item-pages/${fileName}`;  // Carrega a página correspondente
      });

      // Adiciona o quadrado à camada
      layer.add(rect);
    }
  }

  tomadasCheckbox.addEventListener('change', function() {
    if (tomadasCheckbox.checked) {
      // Mostra as cores ao marcar o checkbox
      rects.forEach(({ rect, group }) => {
        if (group === 'availableTomada') {
          rect.fill('rgba(0, 255, 0, 0.8)');  // Verde para tomadas disponíveis
        } else if (group === 'nonAvailableTomada') {
          rect.fill('rgba(255, 0, 0, 0.8)');  // Vermelho para tomadas não disponíveis
        }
      });
    } else {
      // Restaura para transparente ao desmarcar o checkbox
      rects.forEach(({ rect }) => {
        rect.fill('transparent');  // Restaura a cor original (transparente)
      });
    }
    layer.draw();  // Atualiza o desenho
  });

  // Renderiza a camada
  layer.draw();
}

// Função para buscar o status das tomadas e áreas clicáveis do servidor
async function fetchTomadaStatus() {
  try {
    const response = await fetch('load-status.php');
    return await response.json();
  } catch (error) {
    console.error('Erro ao carregar o status das tomadas:', error);
    return { 
      availableTomadas: [], 
      nonAvailableTomadas: [], 
      clickableGroups: { availableClickable: [], nonAvailableClickable: [] } 
    };  // Retorna arrays vazios em caso de erro
  }
}

