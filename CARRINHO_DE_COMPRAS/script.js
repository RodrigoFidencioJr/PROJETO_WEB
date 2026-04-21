const produtos = [
  { nome: "Camiseta Dry Fit", preco: 49.90, imagem: "imgs/camiseta_dryfit.jpg" },
  { nome: "Shorts Esportivo", preco: 39.90, imagem: "imgs/shorts_esportivo.jpg" },
  { nome: "Tênis de Corrida", preco: 199.90, imagem: "imgs/tenis_corrida.jpg" },
  { nome: "Garrafa Térmica", preco: 29.90, imagem: "imgs/garrafa_termica.jpg" },
  { nome: "Luva Academia", preco: 59.90, imagem: "imgs/luva_academia.jpg" },
  { nome: "Mochila Esportiva", preco: 120.00, imagem: "imgs/mochila_esportiva.jpg" },
  { nome: "Meia Esportiva", preco: 19.90, imagem: "imgs/meia_esportiva.jpg" },
  { nome: "Bola de Futebol", preco: 89.90, imagem: "imgs/bola_futebol.jpg" },
  { nome: "Corda de Pular", preco: 25.00, imagem: "imgs/corda_pular.jpg" },
  { nome: "Relógio Esportivo", preco: 250.00, imagem: "imgs/relogio_esportivo.jpg" }
];

let carrinho = [];

// Formata preço (49.9 → 49,90)
const formatarPreco = v => v.toFixed(2).replace(".", ",");

// Mostra produtos na tela
function listarProdutos(lista) {
  const div = document.getElementById("produtos");
  div.innerHTML = "";

  lista.forEach(produto => {
    const item = document.createElement("div");

    item.innerHTML = `
      <div class="produto">
        <img src="${produto.imagem}">
        <p>${produto.nome}</p>
        <p>R$ ${formatarPreco(produto.preco)}</p>
        <button onclick='adicionarCarrinho(${JSON.stringify(produto)})'>
          Adicionar
        </button>
      </div>
    `;

    div.appendChild(item);
  });
}

// Adiciona produto no carrinho
function adicionarCarrinho(produto) {
  const item = carrinho.find(p => p.nome === produto.nome);

  if (item) item.quantidade++;
  else carrinho.push({ ...produto, quantidade: 1 });

  atualizarCarrinho();
}

// Atualiza visual do carrinho
function atualizarCarrinho() {
  const div = document.querySelector("#carrinho");
  const vazio = document.querySelector("#carrinho-vazio");

  // limpa itens antigos
  div.querySelectorAll(".item-carrinho").forEach(el => el.remove());

  // mostra ou esconde mensagem
  vazio.style.display = carrinho.length ? "none" : "block";

  // renderiza itens
  carrinho.forEach((item, i) => {
    const el = document.createElement("div");
    el.classList.add("item-carrinho");

    el.innerHTML = `
      <strong>${item.nome}</strong><br>
      Qtd: ${item.quantidade} | R$ ${formatarPreco(item.preco * item.quantidade)}
      <br>
      <button onclick="removerItem(${i})">Remover</button>
    `;

    div.appendChild(el);
  });

  atualizarTotal();
  salvarCarrinho();
}

// Remove ou diminui item
function removerItem(i) {
  carrinho[i].quantidade > 1
    ? carrinho[i].quantidade--
    : carrinho.splice(i, 1);

  atualizarCarrinho();
}

// Limpa carrinho inteiro
function limparCarrinho() {
  carrinho = [];
  atualizarCarrinho();
}

// Calcula total
function atualizarTotal() {
  const total = carrinho.reduce((s, item) =>
    s + item.preco * item.quantidade, 0
  );

  document.querySelector("#total").innerText = formatarPreco(total);
}

// Salva no localStorage
function salvarCarrinho() {
  localStorage.setItem("carrinho", JSON.stringify(carrinho));
}

// Carrega do localStorage
function carregarCarrinho() {
  const dados = localStorage.getItem("carrinho");
  if (dados) carrinho = JSON.parse(dados);
  atualizarCarrinho();
}

// Filtro de produtos
document.querySelector("#filtro").addEventListener("change", function () {
  const v = this.value;

  const lista =
    v === "ate50" ? produtos.filter(p => p.preco <= 50) :
    v === "acima50" ? produtos.filter(p => p.preco > 50) :
    produtos;

  listarProdutos(lista);
});

// Inicialização
document.addEventListener("DOMContentLoaded", () => {
  listarProdutos(produtos);
  carregarCarrinho();
});