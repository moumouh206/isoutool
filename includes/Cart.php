<?php

class Cart {
    private $db;
    private $user_id;
    private $session_id;
    
    public function __construct($db, $user_id = null) {
        $this->db = $db;
        $this->user_id = $user_id;
        if (!$user_id) {
            if (!isset($_SESSION['session_id'])) {
                $_SESSION['session_id'] = bin2hex(random_bytes(32));
            }
            $this->session_id = $_SESSION['session_id'];
        }
    }
    
    public function addItem($product_id, $quantity = 1) {
        try {
            // Check if product exists and has enough stock
            $stmt = $this->db->prepare('SELECT stock FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                throw new Exception('Product not found');
            }
            
            if ($product['stock'] < $quantity) {
                throw new Exception('Not enough stock available');
            }
            
            // Check if item already exists in cart
            $stmt = $this->db->prepare(
                'SELECT id, quantity FROM cart WHERE product_id = ? AND ' . 
                ($this->user_id ? 'user_id = ?' : 'session_id = ?')
            );
            $stmt->execute([$product_id, $this->user_id ?? $this->session_id]);
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($cartItem) {
                // Update quantity
                $newQuantity = $cartItem['quantity'] + $quantity;
                if ($product['stock'] < $newQuantity) {
                    throw new Exception('Not enough stock available');
                }
                
                $stmt = $this->db->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
                $stmt->execute([$newQuantity, $cartItem['id']]);
            } else {
                // Insert new item
                $stmt = $this->db->prepare(
                    'INSERT INTO cart (product_id, ' . ($this->user_id ? 'user_id' : 'session_id') . ', quantity) 
                     VALUES (?, ?, ?)'
                );
                $stmt->execute([$product_id, $this->user_id ?? $this->session_id, $quantity]);
            }
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function updateQuantity($product_id, $quantity) {
        try {
            // Check if product exists and has enough stock
            $stmt = $this->db->prepare('SELECT stock FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                throw new Exception('Product not found');
            }
            
            if ($product['stock'] < $quantity) {
                throw new Exception('Not enough stock available');
            }
            
            $stmt = $this->db->prepare(
                'UPDATE cart SET quantity = ? WHERE product_id = ? AND ' . 
                ($this->user_id ? 'user_id = ?' : 'session_id = ?')
            );
            $stmt->execute([$quantity, $product_id, $this->user_id ?? $this->session_id]);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function removeItem($product_id) {
        try {
            $stmt = $this->db->prepare(
                'DELETE FROM cart WHERE product_id = ? AND ' . 
                ($this->user_id ? 'user_id = ?' : 'session_id = ?')
            );
            $stmt->execute([$product_id, $this->user_id ?? $this->session_id]);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function clear() {
        try {
            $stmt = $this->db->prepare(
                'DELETE FROM cart WHERE ' . 
                ($this->user_id ? 'user_id = ?' : 'session_id = ?')
            );
            $stmt->execute([$this->user_id ?? $this->session_id]);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function getItems() {
        try {
            $stmt = $this->db->prepare(
                'SELECT c.*, p.name, p.price, p.stock, p.reference, 
                        (p.price * c.quantity) as total_price
                 FROM cart c
                 JOIN products p ON c.product_id = p.id
                 WHERE ' . ($this->user_id ? 'c.user_id = ?' : 'c.session_id = ?')
            );
            $stmt->execute([$this->user_id ?? $this->session_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function getTotal() {
        try {
            $stmt = $this->db->prepare(
                'SELECT SUM(p.price * c.quantity) as total
                 FROM cart c
                 JOIN products p ON c.product_id = p.id
                 WHERE ' . ($this->user_id ? 'c.user_id = ?' : 'c.session_id = ?')
            );
            $stmt->execute([$this->user_id ?? $this->session_id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function transferSessionCartToUser($user_id) {
        try {
            // Begin transaction
            $this->db->beginTransaction();
            
            // Get all items from session cart
            $stmt = $this->db->prepare('SELECT * FROM cart WHERE session_id = ?');
            $stmt->execute([$this->session_id]);
            $sessionItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // For each item in session cart
            foreach ($sessionItems as $item) {
                // Check if item already exists in user's cart
                $stmt = $this->db->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
                $stmt->execute([$user_id, $item['product_id']]);
                $userItem = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userItem) {
                    // Update quantity
                    $stmt = $this->db->prepare('UPDATE cart SET quantity = quantity + ? WHERE id = ?');
                    $stmt->execute([$item['quantity'], $userItem['id']]);
                } else {
                    // Insert item into user's cart
                    $stmt = $this->db->prepare(
                        'INSERT INTO cart (user_id, product_id, quantity)
                         VALUES (?, ?, ?)'
                    );
                    $stmt->execute([$user_id, $item['product_id'], $item['quantity']]);
                }
            }
            
            // Delete session cart
            $stmt = $this->db->prepare('DELETE FROM cart WHERE session_id = ?');
            $stmt->execute([$this->session_id]);
            
            // Commit transaction
            $this->db->commit();
            
            // Update instance variables
            $this->user_id = $user_id;
            $this->session_id = null;
            
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            throw $e;
        }
    }
} 