/**
 * Utilitário de Conversão e Otimização de Imagens no Navegador (Client-Side)
 * Vevey Doces — Projeto TCC
 *
 * Converte imagens JPEG, JPG, PNG automaticamente para WebP otimizado via Canvas API,
 * reduzindo o tamanho em até 90% antes do tráfego de rede ou armazenamento.
 */

window.ImageConverter = {
  /**
   * Converte um arquivo de imagem para WebP otimizado.
   * @param {File|Blob} file - O arquivo de imagem selecionado pelo usuário.
   * @param {Object} options - Configurações de otimização.
   * @param {number} options.maxDimension - Largura/altura máxima em pixels (default: 1200).
   * @param {number} options.quality - Qualidade de compressão WebP de 0 a 1 (default: 0.82).
   * @returns {Promise<Object>} Resultado da conversão com Blob, File, dataUrl e métricas.
   */
  async convertToWebP(file, options = {}) {
    const {
      maxDimension = 1200,
      quality = 0.82
    } = options;

    if (!file || !file.type.startsWith('image/')) {
      throw new Error('O arquivo selecionado não é uma imagem válida.');
    }

    return new Promise((resolve, reject) => {
      const reader = new FileReader();

      reader.onload = (event) => {
        const img = new Image();

        img.onload = () => {
          let { width, height } = img;

          // Redimensionamento proporcional (mantém proporção sem distorção)
          if (width > maxDimension || height > maxDimension) {
            if (width > height) {
              height = Math.round((height * maxDimension) / width);
              width = maxDimension;
            } else {
              width = Math.round((width * maxDimension) / height);
              height = maxDimension;
            }
          }

          // Cria canvas offscreen para renderização com antialiasing
          const canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;

          const ctx = canvas.getContext('2d');
          ctx.imageSmoothingEnabled = true;
          ctx.imageSmoothingQuality = 'high';

          // Fundo branco caso o JPEG/PNG tenha transparência ou artefato
          ctx.fillStyle = '#FFFFFF';
          ctx.fillRect(0, 0, width, height);

          // Desenha a imagem no canvas redimensionado
          ctx.drawImage(img, 0, 0, width, height);

          // Exporta como WebP
          canvas.toBlob((blob) => {
            if (!blob) {
              reject(new Error('Falha ao processar conversão para WebP.'));
              return;
            }

            const originalName = file.name || 'imagem.jpg';
            const baseName = originalName.substring(0, originalName.lastIndexOf('.')) || originalName;
            const newFileName = `${baseName}.webp`;

            const webpFile = new File([blob], newFileName, {
              type: 'image/webp',
              lastModified: Date.now()
            });

            const originalSize = file.size;
            const convertedSize = blob.size;
            const savingsPercent = originalSize > 0 
              ? Math.max(0, Math.round((1 - (convertedSize / originalSize)) * 100))
              : 0;

            const dataUrl = canvas.toDataURL('image/webp', quality);

            resolve({
              file: webpFile,
              blob: blob,
              dataUrl: dataUrl,
              fileName: newFileName,
              originalName: originalName,
              originalSize: originalSize,
              convertedSize: convertedSize,
              originalSizeFormatted: this.formatBytes(originalSize),
              convertedSizeFormatted: this.formatBytes(convertedSize),
              savingsPercent: savingsPercent,
              width: width,
              height: height
            });
          }, 'image/webp', quality);
        };

        img.onerror = () => reject(new Error('Não foi possível ler os dados da imagem.'));
        img.src = event.target.result;
      };

      reader.onerror = () => reject(new Error('Erro na leitura do arquivo local.'));
      reader.readAsDataURL(file);
    });
  },

  /**
   * Formata bytes em formato legível (KB, MB).
   */
  formatBytes(bytes, decimals = 1) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
  }
};
