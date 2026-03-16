const Sale = require("../models/Sale");
const mongoose = require("mongoose");

async function createSale(req, res) {
  try {
    const { userId, productId, productName, quantity, unitPrice } = req.body;

    if (
      userId === undefined ||
      !productId ||
      !productName ||
      quantity === undefined ||
      unitPrice === undefined
    ) {
      return res.status(400).json({
        message: "Todos los campos son obligatorios.",
      });
    }

    const parsedUserId = Number(userId);
    const parsedQuantity = Number(quantity);
    const parsedUnitPrice = Number(unitPrice);

    if (!Number.isInteger(parsedUserId) || parsedUserId <= 0) {
      return res.status(400).json({
        message: "El campo 'userId' debe ser un entero positivo.",
      });
    }

    if (!Number.isInteger(parsedQuantity) || parsedQuantity <= 0) {
      return res.status(400).json({
        message: "El campo 'quantity' debe ser un entero positivo.",
      });
    }

    if (Number.isNaN(parsedUnitPrice) || parsedUnitPrice < 0) {
      return res.status(400).json({
        message: "El campo 'unitPrice' debe ser numérico y no negativo.",
      });
    }

    const totalPrice = parsedQuantity * parsedUnitPrice;

    const sale = await Sale.create({
      userId: parsedUserId,
      productId,
      productName,
      quantity: parsedQuantity,
      unitPrice: parsedUnitPrice,
      totalPrice,
    });

    return res.status(201).json({
      message: "Venta registrada correctamente.",
      sale,
    });
  } catch (error) {
    return res.status(500).json({
      message: "Error al registrar la venta.",
    });
  }
}

async function getAllSales(req, res) {
  try {
    const sales = await Sale.find().sort({ saleDate: -1 });

    return res.status(200).json(sales);
  } catch (error) {
    return res.status(500).json({
      message: "Error al consultar las ventas.",
    });
  }
}

async function getSaleById(req, res) {
  try {
    const { id } = req.params;

    if (!mongoose.Types.ObjectId.isValid(id)) {
      return res.status(400).json({
        message: "El id de la venta no es válido.",
      });
    }

    const sale = await Sale.findById(id);

    if (!sale) {
      return res.status(404).json({
        message: "Venta no encontrada.",
      });
    }

    return res.status(200).json(sale);
  } catch (error) {
    return res.status(500).json({
      message: "Error al consultar la venta.",
    });
  }
}

async function getSalesByUser(req, res) {
  try {
    const { userId } = req.params;
    const parsedUserId = Number(userId);

    if (!Number.isInteger(parsedUserId) || parsedUserId <= 0) {
      return res.status(400).json({
        message: "El userId debe ser un entero positivo.",
      });
    }

    const sales = await Sale.find({ userId: parsedUserId }).sort({ saleDate: -1 });

    return res.status(200).json(sales);
  } catch (error) {
    return res.status(500).json({
      message: "Error al consultar las ventas del usuario.",
    });
  }
}

async function getSalesByDateRange(req, res) {
  try {
    const { startDate, endDate } = req.query;

    if (!startDate || !endDate) {
      return res.status(400).json({
        message: "Los parámetros 'startDate' y 'endDate' son obligatorios.",
      });
    }

    const start = new Date(startDate);
    const end = new Date(endDate);

    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
      return res.status(400).json({
        message: "Las fechas enviadas no son válidas.",
      });
    }

    end.setHours(23, 59, 59, 999);

    if (start > end) {
      return res.status(400).json({
        message: "La fecha inicial no puede ser mayor que la fecha final.",
      });
    }

    const sales = await Sale.find({
      saleDate: {
        $gte: start,
        $lte: end,
      },
    }).sort({ saleDate: -1 });

    return res.status(200).json(sales);
  } catch (error) {
    return res.status(500).json({
      message: "Error al consultar las ventas por rango de fechas.",
    });
  }
}

module.exports = {
  createSale,
  getAllSales,
  getSaleById,
  getSalesByUser,
  getSalesByDateRange,
};