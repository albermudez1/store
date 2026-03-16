const express = require("express");
const requireGatewayToken = require("../middleware/requireGatewayToken");
const {
  createSale,
  getAllSales,
  getSaleById,
  getSalesByUser,
  getSalesByDateRange,
} = require("../controllers/salesController");

const router = express.Router();

router.post("/sales", requireGatewayToken, createSale);
router.get("/sales", requireGatewayToken, getAllSales);
router.get("/sales/:id", requireGatewayToken, getSaleById);
router.get("/sales/user/:userId", requireGatewayToken, getSalesByUser);
router.get("/sales/date-range/search", requireGatewayToken, getSalesByDateRange);

module.exports = router;